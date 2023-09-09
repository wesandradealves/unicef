<?php

namespace Drupal\umio_younger\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to handle quiz methods.
 */
class QuizService {

  /**
   * The machine name of the quiz vocabulary.
   *
   * @var string
   */
  const VOCABULARY = 'quiz_questions';

  /**
   * Define the config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The current user.
   */
  final public function __construct(ConfigFactory $config, EntityTypeManager $entityTypeManager) {
    $this->config = $config;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Get the terms from quiz vocabulary.
   *
   * @return \Drupal\taxonomy\Entity\Term[]
   *   The terms from the quiz vocabulary
   */
  public function getTerms(): array {
    $taxonomyStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $taxonomyStorage->loadByProperties([
      'vid' => self::VOCABULARY,
    ]);

    return $terms;
  }

  /**
   * Function to get the description from skill.
   *
   * @param int $skill
   *   The skill to get the description.
   *
   * @return string
   *   The description from the skill.
   */
  public function getDescriptionFromSkill(int $skill): string {
    $config = $this->config->get('umio_younger.skills_config');

    return $config->get("description_$skill");
  }

  /**
   * Function to get the image from skill.
   *
   * @param int $skill
   *   The skill to get the image.
   *
   * @return string
   *   The image from the skill.
   */
  public function getImageFromSkill(int $skill): ?string {
    $config = $this->config->get('umio_younger.skills_config');

    $fid = $config->get("image_$skill")[0];
    $file = File::load($fid);
    if (!$file) {
      return NULL;
    }

    return $file->createFileUrl(TRUE);
  }

}
