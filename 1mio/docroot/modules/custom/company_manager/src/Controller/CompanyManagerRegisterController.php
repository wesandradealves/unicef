<?php

namespace Drupal\company_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Controller to render twig after submit register company manager webform.
 */
class CompanyManagerRegisterController extends ControllerBase {

  /**
   * Display the success page.
   *
   * @return array
   *   Return markup array.
   */
  public function successRegister() {
    return [
      '#theme' => 'success_page',
      '#title' => $this->t('Registration sent!'),
      '#content' => $this->t('Your registration with full access to 1MIO will be released as soon as we confirm your link with the company informed, ok?'),
      '#cta' => [
        'link' => Url::fromRoute('<front>'),
        'text' => $this->t('What happens in 1MIO'),
      ],
    ];
  }

  /**
   * Display the success page.
   *
   * @return array
   *   Return markup array.
   */
  public function successCityManagerRegister(Node $node) {
    $hasUnicefStamp = $node->get('field_company_unicef_stamp')->getValue()[0]["value"];

    $companyType = $node->get('field_company_type')->getString();
    $title = '';
    if ($companyType === 'city') {
      $title = $this->t('Ready! Registration of the County sent.');
    }
    elseif ($companyType === 'state') {
      $title = $this->t('Ready! Registration of the State sent.');
    }

    return [
      '#theme' => 'success_page_city_manager',
      '#title' => $title,
      '#content' => $this->t('You can follow your account confirmation here on the platform. And just enter your access data.'),
      '#hasUnicefStamp' => $hasUnicefStamp,
      '#cta' => [
        'link' => Url::fromRoute('<front>'),
        'text' => $this->t('What happens in 1MIO'),
      ],
    ];
  }

}
