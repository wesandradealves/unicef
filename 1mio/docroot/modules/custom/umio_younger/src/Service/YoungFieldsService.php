<?php

namespace Drupal\umio_younger\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the construction fields of the Young in form.
 */
class YoungFieldsService {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $formUser;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The current user.
   */
  final public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * The construct method.
   *
   * @param int $uid
   *   The current user.
   */
  public function setFormUser(int $uid) : void {
    $this->formUser = $this->entityTypeManager->getStorage('user')->load($uid);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Creating common fields in the young form.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function createYoungFormFields(array $form, FormStateInterface $form_state): array {

    /** @var \Drupal\user\Entity\User $entity */
    $entity = $this->formUser;

    $this->verifyYoungUserParagraphs($entity);

    $form_state->set('entity', $entity);

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->entityTypeManager->getStorage('entity_form_display')->load('user.user.young_profile');
    $form_state->set('form_display', $form_display);
    $form['#parents'] = [];

    // Get the Image Field.
    $form = $this->getBasicFields($form, $form_state);

    // Get the Image Field.
    $form = $this->getImagesFields($form, $form_state);

    // Get the component fields.
    foreach ($form_display->getComponents() as $name => $component) {
      /** @var \Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget $widget */
      $widget = $form_display->getRenderer($name);
      $items = $entity->get($name);
      $items->filterEmptyItems();
      $form[$name] = $widget->form($items, $form, $form_state);
      $form[$name]['#access'] = $items->access('edit');
    }

    return $form;

  }

  /**
   * Creating the logo and cover fields.
   */
  public function getBasicFields(array $form, FormStateInterface $form_state): array {

    /** @var \Drupal\user\Entity\User $youngUser */
    $youngUser = $this->formUser;

    $youngUserMail = $youngUser->get('mail')->getValue()[0]['value'];
    $form['field_user_email'] = [
      '#type'                 => 'textfield',
      '#title'                => $this->t('Mail'),
      '#default_value'        => $youngUserMail,
      '#attributes' => [
        'disabled' => 'disabled',
        'class' => [],
      ],
    ];

    return $form;
  }

  /**
   * Check if the user has the Curriculum and Social paragraph.
   *
   * @param \Drupal\user\Entity\User $youngUser
   *   Define the user that will pass by the paragraphs checks.
   */
  public function verifyYoungUserParagraphs(User &$youngUser): void {
    $paragraphBones = [
      'field_user_curriculum' => [
        'type' => 'curriculum',
        'field_achievements' => [],
        'field_paragraph_courses' => [],
        'field_paragraph_experience' => [],
        'field_paragraph_education' => [],
        'field_what_do_you_search' => [],
        'field_about_you' => [],
      ],
      'field_user_social' => [
        'type' => 'paragraph_social',
        'field_paragraph_networks' => [],
      ],
      'field_user_address' => [
        'type' => 'paragraph_address',
        'field_paragraph_address' => [
          'langcode' => '',
          'country_code' => 'BR',
          'address_line1' => '',
          'dependent_locality' => '',
          'administrative_area' => '',
          'locality' => '',
          'postal_code' => '',
        ],
      ],

    ];
    if ($youngUser->get('field_user_address')->getValue() !== []) {
      $pararaphId = $youngUser->get('field_user_address')->getValue()[0]['target_id'];
      $paragraph = Paragraph::load($pararaphId);
      $paragraphAddress = $paragraph->get('field_paragraph_address')->getValue();
      $paragraphAddress[0]['postal_code'] = NULL;
      $paragraph->set('field_paragraph_address', $paragraphAddress);
      $paragraph->save();
    }
    foreach ($paragraphBones as $userParagraphField => $paragraphBone) {
      if ($youngUser->get($userParagraphField)->getValue() === []) {
        $paragraph = Paragraph::create($paragraphBone);
        $paragraph->save();
        $paragraphCreated = [
          [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ],
        ];
        $youngUser->set($userParagraphField, $paragraphCreated);
      }
    }
    $youngUser->save();
  }

  /**
   * Creating the logo and cover fields.
   */
  public function getImagesFields(array $form, FormStateInterface $form_state): array {

    /** @var \Drupal\user\Entity\User $youngUser */
    $youngUser = $this->formUser;
    $youngUserPhoto = $youngUser->get('field_user_profile_picture')->getValue();

    $youngUserPhoto = !empty($youngUserPhoto[0]['target_id']) ? ['target_id' => $youngUserPhoto[0]['target_id']] : [];

    $form['field_user_profile_picture'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://user/profile',
      '#upload_validators'    => [
        'file_validate_is_image'      => [],
        'file_validate_extensions'    => ['gif png jpg jpeg'],
        'file_validate_size'          => [2 ** 20 * 5],
      ],
      '#title'                => $this->t('Profile picture'),
      '#description'          => $this->t('Allowed extensions: @extensions', ['@extensions' => 'gif png jpg jpeg']),
      '#default_value'        => $youngUserPhoto,
      '#attributes' => [
        'class' => [
          $form_state->getValue('field_opening') != 'new' ? 'image-preview' : '',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Create public profile fields in the form.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function createPublicProfileFieldsForm(array $form, FormStateInterface $form_state): array {
    /** @var \Drupal\user\Entity\User $entity */
    $entity = $this->formUser;

    $form_state->set('entity', $entity);

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->entityTypeManager->getStorage('entity_form_display')->load('user.user.public_profile');
    $form_state->set('form_display', $form_display);
    $form['#parents'] = [];

    // Get the component fields.
    foreach ($form_display->getComponents() as $name => $component) {
      /** @var \Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget $widget */
      $widget = $form_display->getRenderer($name);
      $items = $entity->get($name);
      $items->filterEmptyItems();
      $form[$name] = $widget->form($items, $form, $form_state);
      $form[$name]['#access'] = $items->access('edit');
      $form[$name]['#attributes']['class'] = 'cb-young-public-profile';

    }

    return $form;
  }

}
