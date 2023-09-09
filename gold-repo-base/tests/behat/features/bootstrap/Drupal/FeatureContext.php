<?php

namespace Drupal;

use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\MarkupContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends MarkupContext implements SnippetAcceptingContext {

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */

  public function __construct() {

  }

  /**
   * @Then I should not see errors
   */
  public function iShouldNotSeeErrors() {
    $errorText = $this->getSession()->getPage()->find('css', '.messages--error');

    if ($errorText !== NULL) {
      $message = $errorText->getText();
      throw new \Exception($message);
    }
    else {
      return;
    }
  }

  /**
   * @Then I should see errors
   */
  public function iShouldSeeErrors() {
    $errorText = $this->getSession()->getPage()->find('css', '.messages');

    if ($errorText !== NULL) {
      return;
    }
    else {
      $message = $errorText->getText();
      throw new \Exception($message);
    }
  }

  /**
   * @Given The element :arg1 should have the attribute :arg2 with a value of
   *   :arg3
   */
  public function theElementShouldHaveTheAttributeWithAValueOf($arg1, $arg2, $arg3) {
    $element = $this->getSession()->getPage()->find('css', $arg1);

    $elementClass = $element->hasClass($arg3);

    if (!$elementClass) {
      throw new \Exception('The element does not have the correct class');
    }
  }


  /**
   * @Given the page title should be :arg1
   */
  public function thePageTitleShouldBe2($arg1) {
    $actTitle = $this->getSession()
      ->getPage()
      ->find('css', 'head title')
      ->getText();
    echo $actTitle;
  }

  protected function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  /**
   * @Then I fill in the Block Quote field with :arg1
   */
  public function iFillInTheBlockQuoteFieldWith2($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-2-subform-field-text-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Given I run cron to change the status
   */
  public function iRunCronToChangeTheStatus() {
    $this->getDriver()->runCron();
  }

  /**
   * @Then I add a schedule for published
   */
  public function iAddAScheduleForPublished() {
    $current_date = date("Y-m-d");
    $future_time = date("h:i:s", time() + 10);
    $add = $this->assertSession()
      ->elementExists('css', '#edit-scheduled-update-actions-ief-add');
    $add->press();
    $date = $this->assertSession()
      ->elementExists('css', 'input[id="edit-scheduled-update-form-inline-entity-form-update-timestamp-0-value-date"]');
    $time = $this->assertSession()
      ->elementExists('css', 'input[id="edit-scheduled-update-form-inline-entity-form-update-timestamp-0-value-time"]');
    $moderation = $this->assertSession()
      ->elementExists('css', 'select[id="edit-scheduled-update-form-inline-entity-form-field-moderation-state"]');
    $date->setValue($current_date);
    $time->setValue($future_time);
    $moderation->setValue('published');
    $save = $this->assertSession()
      ->elementExists('css', '#edit-scheduled-update-form-inline-entity-form-actions-ief-add-save');
    $save->press();
  }

  /**
   * @Then I add a schedule for the past to be published
   */
  public function iAddAScheduleForThePastToBePublished() {
    $current_date = date("Y-m-d");
    $past_time = date("h:i:s", time() - 60);
    $add = $this->assertSession()
      ->elementExists('css', '#edit-scheduled-update-actions-ief-add');
    $add->press();
    $date = $this->assertSession()
      ->elementExists('css', 'input[id="edit-scheduled-update-form-inline-entity-form-update-timestamp-0-value-date"]');
    $time = $this->assertSession()
      ->elementExists('css', 'input[id="edit-scheduled-update-form-inline-entity-form-update-timestamp-0-value-time"]');
    $moderation = $this->assertSession()
      ->elementExists('css', 'select[id="edit-scheduled-update-form-inline-entity-form-field-moderation-state"]');
    $date->setValue($current_date);
    $time->setValue($past_time);
    $moderation->setValue('published');
    $save = $this->assertSession()
      ->elementExists('css', '#edit-scheduled-update-form-inline-entity-form-actions-ief-add-save');
    $save->press();
  }

  /**
   * @Then I should see the Google Tag Manager :arg1 element
   */
  public function iShouldSeeTheGoogleTagManagerElement($arg1) {
    /**
     * @var Behat\Mink\Element\NodeElement $google
     */
    $google = $this->getSession()->getPage()->find('css', 'script');

    // this does not work for URLs
    $link = $google->findLink($arg1);
    if ($link) {
      return;
    }

    // filter by url
    $link = $google->findAll('css', "a[href=\"{$arg1}\"]");
    if ($link) {
      return;
    }
  }

  /**
   * @Then I should see the Google Analytics :arg1 element
   */
  public function iShouldSeeTheGoogleAnalyticsElement($arg1) {
    /**
     * @var Behat\Mink\Element\NodeElement $google
     */
    $google = $this->getSession()->getPage()->find('css', 'script');

    // this does not work for URLs
    $link = $google->findLink($arg1);
    if ($link) {
      return;
    }

    // filter by url
    $link = $google->findAll('css', "a[href=\"{$arg1}\"]");
    if ($link) {
      return;
    }
  }

  /**
   * @Then I should see the data tracked component :arg1 in the :arg2
   */
  public function iShouldSeeTheDataTrackedComponentInTheRegion($arg1, $arg2) {
    $tag = $this->assertSession()->elementExists('css', $arg2);
    if (($tag->hasAttribute('data-tracked-component')) && ($tag->getAttribute('data-tracked-component') == $arg1)) {
      return TRUE;
    }
  }

  /**
   * @Then I should see the data bound :arg1 in the :arg2
   */
  public function iShouldSeeTheDataBoundInTheRegion($arg1, $arg2) {
    $tag = $this->assertSession()->elementExists('css', $arg2);
    if (($tag->hasAttribute('data-bound')) && ($tag->getAttribute('data-bound') == $arg1)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @Then I should see the :arg1 tag with :arg2 value
   */
  public function iShouldSeeTheTagWithValue($arg1, $arg2) {
    $tag = $this->assertSession()->elementExists('css', $arg1);
    if (($tag->hasAttribute($arg1)) && ($tag->getAttribute($arg1) == $arg2)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @When I fill in the Title field with :arg1
   */
  public function iFillInTheTitleFieldWith($arg1) {
    $searchBox = $this->assertSession()
      ->elementExists('css', 'input[id="edit-title-0-value"]');
    $searchBox->setValue($arg1);
  }


  /**
   * @When I fill in the Subtitle field with :arg1
   */
  public function iFillInTheSubtitleFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-subtitle-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the URL Alias field with :arg1
   */
  public function iFillInTheURLAliasFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-path-0-alias"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Variation dropdown with :arg1
   */
  public function iFillInTheVariationDropdownWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-hero-0-subform-field-variation"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button :arg1 to add a new image
   */
  public function iPressTheButtonToAddANewImage($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-image');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Link Text Image section with :arg1
   */
  public function iFillInTheLinkTextImageSectionWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-3-subform-field-link-0-title"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button :arg1
   */
  public function iPressTheButton($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-section');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Teaser field with :arg1
   */
  public function iFillInTheTeaserFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-teaser-description-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Short Title field with :arg1
   */
  public function iFillInTheShortTitleFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-short-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Given I fill in the Teaser Description field with :arg1
   */
  public function iFillInTheTeaserDescriptionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-teaser-description-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Moderation dropdown with :arg1
   */
  public function iFillInTheModerationDropdownWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'select[id="edit-moderation-state-0"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Title Section field with :arg1
   */
  public function iFillInTheTitleSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Subtitle Section field with :arg1
   */
  public function iFillInTheSubtitleSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-text-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button in Text section :arg1
   */
  public function iPressTheButtonInTextSection($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-text');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 in the Column Block section
   */
  public function iPressTheButtonInTheColumnBlockSection($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-column-block');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 also in the Column Block section
   */
  public function iPressTheButtonAlsoInTheColumnBlockSection($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-actions-ief-add');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Title Column Block section field with :arg1
   */
  public function iFillInTheTitleColumnBlockSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the URL Column Block section field with :arg1
   */
  public function iFillInTheUrlColumnBlockSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-cta-0-uri"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Link Text Column Block section field with :arg1
   */
  public function iFillInTheLinkTextColumnBlockSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-cta-0-title"]');
    $var->setValue($arg1);
  }

  /**
   * @When I press the button :arg1 in Column Block section
   */
  public function iPressTheButtonInColumnBlockSection($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-actions-ief-add-save');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Column dropdown with :arg1
   */
  public function iFillInTheColumnDropdownWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-actions-bundle"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the URL Image section with :arg1
   */
  public function iFillInTheUrlImageSectionWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-3-subform-field-link-0-uri"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button Add Block Quote
   */
  public function iPressTheButtonAddBlockQuote() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-block-quote');
    $var->press();
  }

  /**
   * @Then I press the button Create Media
   */
  public function iPressTheButtonCreateMedia() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-actions-ief-add-save');
    $var->press();
  }

  /**
   * @Then I press the button Add Text
   */
  public function iPressTheButtonAddText() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-text');
    $var->press();
  }

  /**
   * @Then I fill in the :arg1 field
   */
  public function iFillInTheField($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-body-0-value"]');
    $var->setValue($arg1);
  }


  /**
   * @Then I press  the button :arg1 to finish the process
   */
  public function iPressTheButtonToFinishTheProcess($arg1) {
    $var = $this->assertSession()->elementExists('css', '#edit-submit');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Media Contacts Title field with :arg1
   */
  public function iFillInTheMediaContactsTitleFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button :arg1 to include a new media contact
   */
  public function iPressTheButtonToIncludeANewMediaContact($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-actions-ief-add');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Organization field with :arg1
   */
  public function iFillInTheOrganizationFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-field-organization-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Job Title field with :arg1
   */
  public function iFillInTheJobTitleFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-field-job-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Email field with :arg1
   */
  public function iFillInTheEmailFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-field-email-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the First Telephone field with :arg1
   */
  public function iFillInTheFirstTelephoneFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-field-telephone-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Secund Telephone field with :arg1
   */
  public function iFillInTheSecundTelephoneFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-field-telephone-1-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I press the button Create Media Contacts to include the new media
   */
  public function iPressTheButtonCreateMediaContactsToIncludeTheNewMedia() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-actions-ief-add');
    $var->press($arg1);
  }

  /**
   * @When I click in the button Add Existing Media Contacts to include another
   *   media
   */
  public function iClickInTheButtonAddExistingMediaContactsToIncludeAnotherMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-actions-ief-add-existing');
    $var->press($arg1);
  }

  /**
   * @When I press the button Create Media Contacts to add the new media
   */
  public function iPressTheButtonCreateMediaContactsToAddTheNewMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-actions-ief-add-save');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 to add the new media
   */
  public function iPressTheButtonToAddTheNewMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-actions-ief-add-save');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 to insert the new media
   */
  public function iPressTheButtonToInsertTheNewMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-actions-ief-add-existing');
    $var->press($arg1);
  }

  /**
   * @When I click in the button :arg1 to include another media
   */
  public function iClickInTheButtonToIncludeAnotherMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-actions-ief-add-existing');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 to update the information
   */
  public function iPressTheButtonToUpdateTheInformation($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-entities-0-actions-ief-entity-edit');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Name field with the new value :arg1
   */
  public function iFillInTheNameFieldWithTheNewValue($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-entities-0-form-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Organization field with the new value :arg1
   */
  public function iFillInTheOrganizationFieldWithTheNewValue($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-entities-0-form-field-organization-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Job Title field with the new value :arg1
   */
  public function iFillInTheJobTitleFieldWithTheNewValue($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-entities-0-form-field-job-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I press the button :arg1 to up to date the infos
   */
  public function iPressTheButtonToUpToDateTheInfos($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-media-contacts-0-subform-field-media-contacts-form-inline-entity-form-entities-0-form-actions-ief-edit-save');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Title of Multimedia Content field with :arg1
   */
  public function iFillInTheTitleOfMultimediaContentFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-multimedia-content-0-subform-field-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the URL of Link section with :arg1
   */
  public function iFillInTheUrlOfLinkSectionWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-multimedia-content-0-subform-field-link-0-uri"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the text field with :arg1
   */
  public function iFillInTheTextFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-1-subform-field-text-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button Add Media Block
   */
  public function iPressTheButtonAddMediaBlock() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-media-block');
    $var->press();
  }

  /**
   * @Then I press the button Add New Media
   */
  public function iPressTheButtonAddNewMedia() {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-2-subform-field-media-item-actions-ief-add');
    $var->press();
  }

  /**
   * @Then I fill in the Media Name field with :arg1
   */
  public function iFillInTheMediaNameFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-name-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Alternative field with :arg1
   */
  public function iFillInTheAlternativeFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-image-0-alt"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Credit field with :arg1
   */
  public function iFillInTheCreditFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-field-credit-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Caption field with :arg1
   */

  public function iFillInTheCaptionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-field-caption-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Variation Media dropdown with :arg1
   */
  public function iFillInTheVariationMediaDropdownWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-2-subform-field-media-variation"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the dropdown with :arg1
   */
  public function iFillInTheDropdownWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-2-subform-field-media-item-actions-bundle"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Media Name with :arg1
   */
  public function iFillInTheMediaNameWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-name-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I fill in the Video URL with :arg1
   */
  public function iFillInTheVideoUrlWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-2-subform-field-media-item-form-inline-entity-form-field-media-video-embed-field-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I press the button :arg1 to include videos
   */
  public function iPressTheButtonToIncludeVideos($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-actions-ief-add');
    $var->press($arg1);
  }

  /**
   * @When I press the button :arg1 to include medias
   */
  public function iPressTheButtonToIncludeMedias($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-video-actions-ief-add');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Video URL field with :arg1
   */
  public function iFillInTheVideoUrlFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-field-media-video-embed-field-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button :arg1 to include the media
   */
  public function iPressTheButtonToIncludeTheMedia($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-actions-ief-add-save');
    $var->press($arg1);
  }

  /**
   * @When I fill in the Title of Media section field with :arg1
   */
  public function iFillInTheTitleOfMediaSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-title-0-value"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the URL of Media section field with :arg1
   */
  public function iFillInTheUrlOfMediaSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-cta-0-uri"]');
    $var->setValue($arg1);
  }

  /**
   * @When I fill in the Link Text of Media section field with :arg1
   */
  public function iFillInTheLinkTextOfMediaSectionFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-field-cta-0-title"]');
    $var->setValue($arg1);
  }

  /**
   * @Then I press the button :arg1 to include Column Content
   */
  public function iPressTheButtonToIncludeColumnContent($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-1-subform-field-column-content-form-inline-entity-form-actions-ief-add-save');
    $var->press($arg1);
  }

  /**
   * @Then I press the button :arg1 to include Block Quote section
   */
  public function iPressTheButtonToIncludeBlockQuoteSection($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-block-quote');
    $var->press($arg1);
  }

  /**
   * @When I go to create a Basic Page
   */
  public function iGoToCreateABasicPage() {
    $this->getSession()->visit($this->locatePath('/node/add/page'));
  }

  /**
   * @When I go to create a Photo Essay
   */
  public function iGoToCreateAPhotoEssay() {
    $this->getSession()->visit($this->locatePath('/node/add/photo_essay'));
  }

  /**
   * @Given I create/update the :arg1 with
   */
  public function iCreateTheWith(TableNode $table) {
    $values = [];
    $fieldString = '';

    /**
     * This fieldmap converts the table field names to their HTMLDom counterparts
     */
    $fieldmap = [
      'Title' => 'input[id="edit-title-0-value"]',
      'Subtitle' => 'input[id="edit-field-subtitle-0-value"]',
      'URLalias' => 'input[id="edit-path-0-alias"]',
      'Variation' => 'select[id="edit-field-hero-0-subform-field-variation"]',
      'Moderation' => 'select[id="edit-moderation-state-0"]',
      'ShortTitle' => 'input[id="edit-field-short-title-0-value"]',
      'Teaser' => 'input[id="edit-field-teaser-description-0-value"]',
      'Author' => 'input[id="edit-field-author-0-value"]',
      'GlobalCategory' => 'input[id="edit-field-category-0-target-id"]',
      'GeographicalCategory' => 'input[id="edit-field-geographical-terms-0-target-id"]',
    ];

    foreach ($table as $item) {
      // Removing spaces from string to avoid issues with assoc array... just in case
      $fieldString = str_replace(' ', '', $item['Field']);

      $field = $this->assertSession()
        ->elementExists('css', $fieldmap[$fieldString]);
      $field->setValue($item['Value']);
    }
  }

  /**
   * @When I update the :arg1 with the following character lengths
   */
  public function iUpdateTheWithTheFollowingCharacterLengths(TableNode $table) {
    $modifiedTable = [0 => ['Field', 'Value']];

    $newTable = NULL;

    foreach ($table as $item) {
      $matches = [];
      $row = [];

      $row[0] = $item['Field'];
      preg_match("/r\(([0-9]*)\)/", $item['Value'], $matches);

      if (count($matches) > 0) {
        $strln = (int) $matches[1];
        $randomString = $this->generateRandomString($strln);

        $row[1] = $randomString;
      }
      else {
        $row[1] = $item['Value'];
      }

      array_push($modifiedTable, $row);
    }

    $newTable = new TableNode($modifiedTable);

    $this->iCreateTheWith($newTable);
  }



  ################################## COMPONENTS ##################################

  /**
   * @When I add\/modify a Mosaic on the component :arg1 of the :arg2
   */
  public function iAddModifyAMosaicOnTheComponentOfThe($arg1, $arg2, TableNode $table) {
    $values = [];
    $fieldString = '';
    $componentString = '';

    if ($arg2 == 'Visual Components') {
      $componentString = 'edit-field-paragraph-0-subform';
    }

    $fieldmap = [
      'Title' => 'input[data-drupal-selector="' . $componentString . '-field-components-' . $arg1 . '-subform-field-title-0-value"]',
      'Description' => 'textarea[data-drupal-selector="' . $componentString . '--field-components-' . $arg1 . '-subform-field-body-0-value"]',
      'Feed' => 'select[data-drupal-selector="' . $componentString . '-field-components-' . $arg1 . '-subform-field-feed"]',
      'Category' => 'input[id="' . $componentString . '-field-components-' . $arg1 . '-subform-field-category-0-target-id"]',
      'Content' => 'input[id="' . $componentString . '-field-components-' . $arg1 . '-subform-field-content-0-target-id"]',
    ];

    foreach ($table as $item) {
      // Removing spaces from string to avoid issues with assoc array... just in case
      $fieldString = str_replace(' ', '', $item['Field']);

      $field = $this->assertSession()
        ->elementExists('css', $fieldmap[$fieldString]);
      $field->setValue($item['Value']);
    }
  }

  /**
   * @When I fill in the IFrame Title field with :arg1
   */
  public function iFillInTheIFrameTitleFieldWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-embed-0-subform-field-iframe-0-title"]');
    $var->setValue($arg1);
  }


  /**
   * @When I add\/modify a Wrapper on the component :arg1 of the :arg2
   */
  public function iAddModifyAWrapperOnTheComponentOfThe($arg1, $arg2, TableNode $table) {
    $values = [];
    $fieldString = '';
    $componentString = '';

    if ($arg2 == 'Visual Components') {
      $componentString = 'edit-field-embed';
    }

    $fieldmap = [
      'URL' => 'input[data-drupal-selector="' . $componentString . '-' . $arg1 . '-subform-field-iframe-0-url"]',
      'Title' => 'input[data-drupal-selector="' . $componentString . '-' . $arg1 . '-subform-field-iframe-0-title"]',
      'Width' => 'input[data-drupal-selector="' . $componentString . '-' . $arg1 . '-subform-field-iframe-0-width"]',
      'Height' => 'input[data-drupal-selector="' . $componentString . '-' . $arg1 . '-subform-field-iframe-0-height"]',
    ];

    foreach ($table as $item) {
      // Removing spaces from string to avoid issues with assoc array... just in case
      $fieldString = str_replace(' ', '', $item['Field']);

      $field = $this->assertSession()
        ->elementExists('css', $fieldmap[$fieldString]);
      $field->setValue($item['Value']);
    }
  }


  /**
   * @Then I create a Banner with
   */
  public function iCreateABannerWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-banner');
    $var->press();

    $titleB = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $teaser = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-teaser-0-value"]');
    $style = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-0-subform-field-banner-style"]');
    $acstyle = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-0-subform-field-call-to-action-style"]');
    $url = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-call-to-action-0-uri"]');
    $linktext = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-call-to-action-0-title"]');

    $titleB->setValue('Banner Title');
    $teaser->setValue('Banner Teaser');
    $style->setValue('media-regular');
    $acstyle->setValue('blue');
    $url->setValue('<front>');
    $linktext->setValue('Banner Link Text');
  }

  /**
   * @Then I create a Block Quote with
   */
  public function iCreateABlockQuoteWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-block-quote');
    $var->press();

    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-text-0-value"]');
    $title->setValue('Block Quote Text');
  }

  /**
   * @Then I create an image Column Block with
   */
  public function iCreateAnImageColumnBlockWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-column-block');
    $var->press();

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-actions-ief-add');
    $var->press();

    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-title-0-value"]');
    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-body-0-value"]');
    $url = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-cta-0-uri"]');
    $linktext = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-cta-0-title"]');

    $title->setValue('Image Column Title');
    $body->setValue('Image Media Body');
    $url->setValue('<front>');
    $linktext->setValue('Image Media Link Text');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-actions-ief-add-save');
    $var->press();
  }

  /**
   * @Then I create a video Column Block with
   */
  public function iCreateAVideoColumnBlockWith(TableNode $table) {
    $type = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-actions-bundle"]');
    $type->setValue('column_video');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-actions-ief-add');
    $var->press();

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-actions-ief-add');
    $var->press();

    $medianame = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-name-0-value"]');
    $video = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-field-media-video-embed-field-0-value"]');
    $caption = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-field-caption-0-value"]');
    $credit = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-field-credit-0-value"]');

    $medianame->setValue('Media Name');
    $video->setValue('https://www.youtube.com/watch?v=40y8l1Wzpe0');
    $caption->setValue('Caption');
    $credit->setValue('Credit');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-video-form-inline-entity-form-actions-ief-add-save');
    $var->press();

    $mediatitle = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-title-0-value"]');
    $mediabody = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-body-0-value"]');
    $mediaurl = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-cta-0-uri"]');
    $medialink = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-field-cta-0-title"]');

    $mediatitle->setValue('Video Column Title');
    $mediabody->setValue('Video Media Body');
    $mediaurl->setValue('<front>');
    $medialink->setValue('Video Media Link Text');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-actions-ief-add-save');
    $var->press();
  }

  /**
   * @Then I create a CTA Block with
   */
  public function iCreateACTABlockWith(TableNode $table, $containerType = 'paragraph') {
    $page = $this->getSession()->getPage();

    // On other pages, the CTA block will be part of visual components, but on some pages it won't be.
    // This checks for the existence of the button before it tries to click it, so the behat test doesn't fail.
    if (!empty($page->find('css', '#edit-field-paragraph-add-more-add-more-button-cta-block'))) {
      $var = $this->assertSession()
        ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-cta-block');
      $var->press();
    }

    $values = [];
    $fieldString = '';

    /**
     * This fieldmap converts the table field names to their HTMLDom counterparts
     */
    $fieldmap = [
      'Title' => 'input[id="edit-field-' . $containerType . '-0-subform-field-title-0-value"]',
      'Text' => 'textarea[id="edit-field-' . $containerType . '-0-subform-field-body-0-value"]',
      'BlockStyle' => 'select[id="edit-field-' . $containerType . '-0-subform-field-block-style"]',
      'TitleEntities' => 'input[id="edit-field-' . $containerType . '-0-subform-field-cta-form-inline-entity-form-title-0-value"]',
      'Url' => 'input[id="edit-field-' . $containerType . '-0-subform-field-cta-form-inline-entity-form-field-link-0-uri"]',
      'LinkText' => 'input[id="edit-field-' . $containerType . '-0-subform-field-cta-form-inline-entity-form-field-link-0-title"]',
      'Style' => 'select[id="edit-field-' . $containerType . '-0-subform-field-cta-form-inline-entity-form-field-style"]',
    ];

    // var_dump($table);
    // throw new PendingException();

    foreach ($table as $item) {
      // Removing spaces from string to avoid issues with assoc array... just in case
      $fieldString = str_replace(' ', '', $item['Field']);

      $field = $this->assertSession()
        ->elementExists('css', $fieldmap[$fieldString]);
      $field->setValue($item['Value']);
    }
  }

  /**
   * @Then I create a CTA Block on ELP with
   */
  public function iCreateACTABlockOnELPWith(TableNode $table) {
    $this->iCreateACTABlockWith($table, 'cta');
  }

  /**
   * @Then I create a Hero with
   */
  public function iCreateAHeroWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-hero');
    $var->press();

    $variation = $this->assertSession()
      ->elementExists('css', 'select[id="edit-field-paragraph-0-subform-field-variation"]');
    $author = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-author-0-value"]');
    $credit = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-text-0-value"]');

    $variation->setValue('hero--no-image');
    $author->setValue('Unicef Author');
    $credit->setValue('Unicef Credit');
  }

  /**
   * @Then I create a Section with
   */
  public function iCreateASectionWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-add-more-add-more-button-section');
    $var->press();

    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $subtitle = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-text-0-value"]');

    $title->setValue('Section Title');
    $subtitle->setValue('Section Subtitle');
  }

  /**
   * @Then I create a Text with
   */
  public function iCreateATextWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-text');
    $var->press();

    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-body-0-value"]');

    $body->setValue('Text Body');
  }

  /**
   * @Then I create a Multimedia with
   */
  public function iCreateAMultimediaWith(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-add-more-add-more-button-multimedia-content');
    $var->press();

    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-title-0-value"]');
    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-body-0-value"]');
    $url = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-link-0-uri"]');
    $linktext = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-link-0-title"]');

    $title->setValue('Multimedia Content Title');
    $body->setValue('Multimedia Content Body');
    $url->setValue('<front>');
    $linktext->setValue('Multimedia Content Link Text');
  }

  ################################## COMPONENTS TRANSLATIONS ##################################

  /**
   * @Then I translate the main fields to
   */
  public function iTranslateTheMainFieldsTo(TableNode $table) {
    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-title-0-value"]');
    $subtitle = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-subtitle-0-value"]');
    $url = $this->assertSession()
      ->elementExists('css', 'input[id="edit-path-0-alias"]');

    $title->setValue('Título da Unicef');
    $subtitle->setValue('Um garoto da Síria');
    $url->setValue('/basic-page/como-trabalhamos');
  }

  /**
   * @Then I translate the Banner fields to
   */
  public function iTranslateTheBannerFieldsTo(TableNode $table) {
    $titleB = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $teaser = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-teaser-0-value"]');

    $titleB->setValue('Título do Banner');
    $teaser->setValue('Teaser do Banner');
  }

  /**
   * @Then I translate the Block Quote fields to
   */
  public function iTranslateTheBlockQuoteFieldsTo(TableNode $table) {
    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-text-0-value"]');
    $title->setValue('Texto do Block Quote');
  }

  /**
   * @Then I translate the Column Block fields to
   */
  public function iTranslateTheColumnBlockFieldsTo(TableNode $table) {
    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-entities-0-actions-ief-entity-edit');
    $var->press();

    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-0-form-title-0-value"]');
    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-0-form-field-body-0-value"]');
    $linktext = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-0-form-field-cta-0-title"]');

    $title->setValue('Título da Imagem da Media');
    $body->setValue('Corpo do Imagem da Media');
    $linktext->setValue('Link do Texto da Imagem da Media');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-0-form-actions-ief-edit-save');
    $var->press();

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-entities-1-actions-ief-entity-edit');
    $var->press();

    $mediatitle = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-1-form-title-0-value"]');
    $mediabody = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-1-form-field-body-0-value"]');
    $medialink = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-1-form-field-cta-0-title"]');

    $mediatitle->setValue('Título do Video da Media');
    $mediabody->setValue('Corpo do Video da Media');
    $medialink->setValue('Link do Texto do Video da Media');

    $var = $this->assertSession()
      ->elementExists('css', '#edit-field-paragraph-0-subform-field-components-0-subform-field-column-content-form-inline-entity-form-entities-1-form-actions-ief-edit-save');
    $var->press();
  }

  /**
   * @Then I translate the CTA Block fields to
   */
  public function iTranslateTheCTABlockFieldsTo(TableNode $table) {
    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $text = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-body-0-value"]');

    $title->setValue('Título do CTA Block');
    $text->setValue('Corpo do CTA Block');
  }

  /**
   * @Then I translate the Hero fields to
   */
  public function iTranslateTheHeroFieldsTo(TableNode $table) {
    $author = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-author-0-value"]');
    $author->setValue('Autor da Unicef');
  }

  /**
   * @Then I translate the Section fields to
   */
  public function iTranslateTheSectionFieldsTo(TableNode $table) {
    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-title-0-value"]');
    $subtitle = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-text-0-value"]');

    $title->setValue('Título da Section');
    $subtitle->setValue('Subtítulo da Section');
  }

  /**
   * @Then I translate the Text fields to
   */
  public function iTranslateTheTextFieldsTo(TableNode $table) {
    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-body-0-value"]');

    $body->setValue('Texto do Body');
  }

  /**
   * @Then I translate the Multimedia fields to
   */
  public function iTranslateTheMultimediaFieldsTo(TableNode $table) {
    $title = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-title-0-value"]');
    $body = $this->assertSession()
      ->elementExists('css', 'textarea[id="edit-field-paragraph-0-subform-field-components-0-subform-field-body-0-value"]');
    $linktext = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-paragraph-0-subform-field-components-0-subform-field-link-0-title"]');

    $title->setValue('Multimedia Content Título');
    $body->setValue('Multimedia Content Corpo');
    $linktext->setValue('Multimedia Content Link do Texto');
  }

  /**
   * @Then I press Save to finish the process
   */
  public function iPressSaveToFinishTheProcess() {
    $var = $this->assertSession()->elementExists('css', '#edit-submit');
    $var->press();
  }

  /**
   * @When I fill in the Link text of Link section with :arg1
   */
  public function iFillInTheLinkTextOfLinkSectionWith($arg1) {
    $var = $this->assertSession()
      ->elementExists('css', 'input[id="edit-field-multimedia-content-0-subform-field-link-0-title"]');
    $var->setValue($arg1);
  }

  /**
   * Checks that page markup contains specified text.
   *
   * @Then /^(?:|I )should have "(?P<text>(?:[^"]|\\")*)" in the markup$/
   */
  public function assertMarkupContainsText($text) {
    if (strstr($this->getSession()->getPage()->getContent(), $text) === FALSE) {
      throw new ExpectationException(sprintf('Text %s$ could not be found', $text), $this->getSession()->getDriver());
    }
  }

}



