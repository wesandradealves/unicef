<?php

namespace Drupal\umio_helpers\Plugin\SocialMediaLinks\Iconset;

use Drupal\social_media_links\IconsetBase;
use Drupal\social_media_links\IconsetInterface;

/**
 * Provides 'elegantthemes' iconset.
 *
 * @Iconset(
 *   id = "phosphor",
 *   publisher = "Phosphor Icons",
 *   publisherUrl = "https://phosphoricons.com/",
 *   downloadUrl = "https://github.com/phosphor-icons/phosphor-home",
 *   name = "Phosphor icons",
 * )
 */
class Phosphor extends IconsetBase implements IconsetInterface {

  /**
   * {@inheritdoc}
   */
  public function setPath($iconset_id) : void {
    $this->path = $this->finder->getPath($iconset_id) ? $this->finder->getPath($iconset_id) : 'library';
  }

  /**
   * {@inheritdoc}
   */
  public function getStyle() {
    return [
      '2x' => 'ph-2x',
      '3x' => 'ph-3x',
      '4x' => 'ph-4x',
      '5x' => 'ph-5x',
    ];
  }

  /**
   * Get icon.
   *
   * @param string $platform
   *   The platform icon.
   * @param string $style
   *   The size.
   */
  public function getIconElement($platform, $style) {
    /** @var \Drupal\social_media_links\PlatformBase $platform */
    $icon_name = $platform->getIconName();

    $iconsArray = [
      'bitbucket' => 'git-branch',
      'discord' => 'discord-logo',
      'email' => 'envelope',
      'facebook' => 'facebook-logo',
      'github' => 'github-logo',
      'gitlab' => 'gitlab-logo',
      'googleplus' => 'google-logo',
      'instagram' => 'instagram-logo',
      'linkedin' => 'linkedin-logo',
      'twitter' => 'twitter-logo',
      'tiktok' => 'tiktok-logo',
      'youtube' => 'youtube-logo',
      'rss' => 'rss',
      'pinterest' => 'pinterest-logo',
      'whatsapp' => 'whatsapp-logo',
      'website' => 'house-simple',
      'slideshare' => 'x',

      'vimeo' => 'x',
      'behance' => 'x',
      'flickr' => 'x',
      'xing' => 'x',
      'tumblr' => 'x',
      'vk' => 'x',
      'drupal' => 'x',
    ];

    $icon = [
      '#type' => 'markup',
      '#markup' => "<i class='ph-$iconsArray[$icon_name] ph-$style'></i>",
    ];

    return $icon;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    return [
      'social_media_links/phosphoricons.component',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIconPath($icon_name, $style) {
    return '';
  }

}
