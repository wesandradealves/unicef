<?php

namespace Drupal\umio_user\Service;

/**
 * Class to centralize the social feed methods.
 */
class SocialService {

  /**
   * Get timestamp and return date time in the social format.
   *
   * @param int $timeStamp
   *   The created time stamp.
   *
   * @return string
   *   Return the dateTime Value
   */
  public function getNodeCreatedField(int $timeStamp): string {
    $createdTime = new \DateTime();
    $createdTime->setTimestamp($timeStamp);
    $diff = $createdTime->diff(new \DateTime('now'));
    if ($diff->days) {
      $dateTime = $diff->days === 1 ? $diff->days . ' dia' : $diff->days . ' dias';
    }
    elseif ($diff->h) {
      $dateTime = $diff->h . ' h';
    }
    else {
      $dateTime = $diff->i . ' min';
    }
    return $dateTime;
  }

}
