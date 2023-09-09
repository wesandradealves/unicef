<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Form to save the text and image for the younger skills.
 */
class SkillsConfigForm extends ConfigFormBase {

  /**
   * The number of skills.
   *
   * @var int
   */
  const NUMBER_OF_SKILLS = 4;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'umio_younger.skills_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'skills_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('umio_younger.skills_config');
    for ($i = 1; $i <= self::NUMBER_OF_SKILLS; $i++) {
      $form["skill_$i"] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Skill @id', ['@id' => $i]),
      ];
      $form["skill_$i"]["description_$i"] = [
        '#type' => 'textarea',
        '#title' => $this->t('Skill description @id', ['@id' => $i]),
        '#default_value' => $config->get("description_$i"),
        '#required' => TRUE,
      ];
      $form["skill_$i"]["image_$i"] = [
        '#type' => 'managed_file',
        '#upload_location'      => 'public://',
        '#upload_validators'    => [
          'file_validate_is_image'      => [],
          'file_validate_extensions'    => ['png jpg jpeg'],
        ],
        '#title' => $this->t('Skill image @id', ['@id' => $i]),
        '#default_value' => $config->get("image_$i"),
        '#required' => TRUE,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $config = $this->config('umio_younger.skills_config');
    for ($i = 1; $i <= self::NUMBER_OF_SKILLS; $i++) {
      $fid = $form_state->getValue("image_$i")[0];
      $file = File::load($fid);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $config->set("description_$i", $form_state->getValue("description_$i"));
        $config->set("image_$i", $form_state->getValue("image_$i"));
      }
      else {
        $form_state->setErrorByName("image_$i", $this->t("An error ocurred trying to save the image."));
      }
    }
    $config->save();
  }

}
