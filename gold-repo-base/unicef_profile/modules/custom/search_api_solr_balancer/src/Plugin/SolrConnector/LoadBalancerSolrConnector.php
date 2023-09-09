<?php

namespace Drupal\search_api_solr_balancer\Plugin\SolrConnector;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\search_api_solr\Plugin\SolrConnector\BasicAuthSolrConnector;
use Drupal\search_api_solr\SearchApiSolrException;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Solarium\Core\Client\Adapter\Http;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load Balancer Solr connector.
 *
 * @SolrConnector(
 *   id = "load_balancer",
 *   label = @Translation("Load Balancer"),
 *   description = @Translation("A connector usable for balancing different Solr instances.")
 * )
 */
class LoadBalancerSolrConnector extends BasicAuthSolrConnector implements ContainerFactoryPluginInterface {

  const FAILOVER_MIN_DURATION = 120;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The Current User Context service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.default'),
      $container->get('current_user')
    );

    $plugin->eventDispatcher = \Drupal::service('event_dispatcher');

    return $plugin;
  }

  /**
   * LoadBalancerSolrConnector constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache service for being able to cache the faulty status.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user for permission checks.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $cache, AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
    $this->cache = $cache;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Allows to supply extra Solr instances for balancing.
   *
   * @param array $form
   *   The Drupal Form API array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The Drupal Form API array.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $instances_form = $form;

    foreach ($this->configuration as $k => $v) {
      if (strstr($k, 'balanced-') !== -1) {
        $orig_key = str_replace('balanced-', '', $k);
        $this->configuration[$orig_key] = $v;
        if (!isset($this->configuration[$orig_key]) || !isset($instances_form[$orig_key]['#default_value'])) {
          continue;
        }
        $instances_form[$orig_key]['#default_value'] = $this->configuration[$k];
      }
    }

    $form['balanced'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Extra Solr instance'),
      '#collapsible' => TRUE,
    ];
    $form['balanced'] = array_merge($form['balanced'], $instances_form);

    return $form;
  }

  /**
   * Saves the load balancer configuration.
   *
   * @param array $form
   *   The Drupal Form API array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form wih the submitted values.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    foreach ($values['balanced'] as $key => $value) {
      if (!is_array($value)) {
        $form_state->setValue('balanced-' . $key, $value);
      }
    }
    foreach ($values['balanced']['auth'] as $key => $value) {
      if (!is_array($value)) {
        $form_state->setValue('balanced-' . $key, $value);
      }
    }
    $form_state->unsetValue('balanced');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Prepares the connection to the Solr server.
   *
   * @param bool $force_fail
   *   If set, either it forces to connect to master or to failover.
   */
  protected function connect($force_fail = NULL) {
    $use_primary = TRUE;
    if ($this->solr && $force_fail === NULL) {
      return;
    }

    if ($force_fail === NULL) {
      // By 50% chance, the failover server is utilized.
      if (rand(0, 1)) {
        $use_primary = FALSE;
        $this->activateBalancer();
      }
    }
    elseif ($force_fail === TRUE) {
      $use_primary = FALSE;
      $this->activateBalancer();
    }
    elseif ($force_fail === FALSE && isset($this->configuration_original)) {
      $this->configuration = $this->configuration_original;
    }

    // Let's check if our selected server is alive, if not, force-switch
    // to the hopefully healthy one.
    if (!isset($force_fail)) {

      if (!$this->isAlive()) {
        if ($use_primary) {
          return $this->connect(TRUE);
        }
        else {
          return $this->connect(FALSE);
        }

      }
    }
    $configuration = $this->configuration;
    $configuration['query_timeout'] = $configuration['timeout'] ?? 5;
    $adapter = extension_loaded('curl') ? new Curl($configuration) : new Http($configuration);
    unset($configuration['timeout']);
    $this->solr = new Client($adapter, $this->eventDispatcher);
    $this->solr->createEndpoint($this->configuration + ['key' => 'search_api_solr'], TRUE);
  }

  /**
   * Checks if currently it is possible to connect to the server.
   */
  protected function isAlive() {
    $cid = md5(serialize($this->configuration));
    if ($cache = $this->cache
      ->get($cid)) {
      return $cache->data;
    }

    try {
      $configuration = $this->configuration;
      $configuration['query_timeout'] = $configuration['timeout'] ?? 5;
      $adapter = extension_loaded('curl') ? new Curl($configuration) : new Http($configuration);
      unset($configuration['timeout']);
      $this->solr = new Client($adapter, $this->eventDispatcher);
      $this->solr->createEndpoint($this->configuration + ['key' => 'search_api_solr'], TRUE);
      $ping = $this->solr->createPing();
      $this->solr->ping($ping);
    }
    catch (\Exception $e) {
      $this->cache
        ->set($cid, FALSE, time() + self::FAILOVER_MIN_DURATION);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Activates the load balancing by updating the current configuration.
   */
  protected function activateBalancer() {
    $this->configuration_original = $this->configuration;
    foreach ($this->configuration as $k => $v) {
      if (strstr($k, 'balanced-') !== -1) {
        $orig_key = str_replace('balanced-', '', $k);
        $this->configuration[$orig_key] = $v;
      }
    }
  }

  /**
   * Disallow Solr updates when master is not available.
   *
   * @throws \Drupal\search_api_solr\SearchApiSolrException
   */
  public function getUpdateQuery() {
    if (!$this->isAlive()) {
      throw new SearchApiSolrException('The master Solr instance is not available, updates are not allowed.');
    }
    return parent::getUpdateQuery();
  }

  /**
   * {@inheritdoc}
   */
  public function viewSettings() {
    $this->connect(FALSE);
    $primary_alive = $this->isAlive();
    $info[] = [
      'label' => $this->t('Primary Solr server URI'),
      'info' => $this->getServerLink(),
    ];
    $info[] = [
      'label' => $this->t('Primary Solr server status'),
      'info' => $primary_alive ? $this->t('Available') : $this->t('Not available'),
    ];

    $this->connect(TRUE);
    $secondary_alive = $this->isAlive();
    $info[] = [
      'label' => $this->t('Secondary Solr server URI'),
      'info' => $this->getServerLink(),
    ];
    $info[] = [
      'label' => $this->t('Secondary Solr server status'),
      'info' => $secondary_alive ? $this->t('Available') : $this->t('Not available'),
    ];

    return $info;
  }

}
