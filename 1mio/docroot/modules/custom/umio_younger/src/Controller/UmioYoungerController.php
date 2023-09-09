<?php

namespace Drupal\umio_younger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\umio_younger\Service\QuizService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for umio_younger routes.
 */
class UmioYoungerController extends ControllerBase {

  /**
   * The quiz service.
   *
   * @var \Drupal\umio_younger\Service\QuizService
   */
  private $quizService;

  /**
   * The construct method.
   *
   * @param \Drupal\umio_younger\Service\QuizService $quizService
   *   The current user.
   */
  final public function __construct(QuizService $quizService) {
    $this->quizService = $quizService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_younger.quiz_service')
    );
  }

  /**
   * Build the success page response.
   *
   * @return array
   *   Return markup array.
   */
  public function successAdditionalRegister(): array {
    $user = User::load($this->currentUser()->id());
    $userName = $user->get('field_user_name')->getString();
    return [
      '#theme' => 'page_register_young_success',
      '#userName' => $userName,
    ];
  }

  /**
   * Build the young skills page.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Return markup array.
   */
  public function youngerSkillsPage() {
    $user = User::load($this->currentUser()->id());
    $userSkills = $user->get('field_younger_skills')->getValue();
    if (empty($userSkills)) {
      return $this->redirect('umio_younger.quiz_form');
    }

    $skills = [];
    foreach ($userSkills as $skill) {
      if (isset($skill['value'])) {
        $skillValue = (int) $skill['value'];
        $skills[] = [
          'title' => $user->get('field_younger_skills')->getSetting('allowed_values')[$skillValue],
          'text' => $this->quizService->getDescriptionFromSkill($skillValue),
          'image' => $this->quizService->getImageFromSkill($skillValue),
        ];
      }
    }
    return [
      '#theme' => 'page_young_skills',
      '#skills' => $skills,
    ];
  }

}
