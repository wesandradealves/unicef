<?php

namespace Drupal\umio_younger\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;
use Drupal\umio_younger\Service\YoungService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Feed not found results' Block.
 *
 * @Block(
 *   id = "feed_not_found_results_block",
 *   admin_label = @Translation("Feed not found results"),
 *   category = @Translation("Content"),
 * )
 */
class FeedNotFoundResultsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Define the current user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  private $account;

  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRoute;

  /**
   * The young service.
   *
   * @var \Drupal\umio_younger\Service\YoungService
   */
  private $youngService;

  /**
   * Constructs a FeedNotFoundResultsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The current user.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRoute
   *   The current route.
   * @param \Drupal\umio_younger\Service\YoungService $youngService
   *   The young service.
   */
  final public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    AccountProxy $account,
    CurrentRouteMatch $currentRoute,
    YoungService $youngService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->currentRoute = $currentRoute;
    $this->youngService = $youngService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('umio_younger.young_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $routeName = $this->currentRoute->getCurrentRouteMatch()->getRouteName();
    if ($routeName === 'view.feeds_jovens.feed_skills') {
      $skills = $this->youngService->getSkillsFromTheUser($this->account);
      return [
        '#theme' => 'feed_not_found_results',
        '#skills' => $skills,
      ];
    }

    return [
      '#theme' => 'feed_not_found_results',
    ];
  }

}
