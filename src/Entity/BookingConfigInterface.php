<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Booking config entities.
 *
 * @ingroup booking_system
 */
interface BookingConfigInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Booking config name.
   *
   * @return string
   *   Name of the Booking config.
   */
  public function getName();

  /**
   * Sets the Booking config name.
   *
   * @param string $name
   *   The Booking config name.
   *
   * @return \Drupal\booking_system\Entity\BookingConfigInterface
   *   The called Booking config entity.
   */
  public function setName($name);

  /**
   * Gets the Booking config creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Booking config.
   */
  public function getCreatedTime();

  /**
   * Sets the Booking config creation timestamp.
   *
   * @param int $timestamp
   *   The Booking config creation timestamp.
   *
   * @return \Drupal\booking_system\Entity\BookingConfigInterface
   *   The called Booking config entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Booking config revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Booking config revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\booking_system\Entity\BookingConfigInterface
   *   The called Booking config entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Booking config revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Booking config revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\booking_system\Entity\BookingConfigInterface
   *   The called Booking config entity.
   */
  public function setRevisionUserId($uid);

}
