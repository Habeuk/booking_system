<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Booking equipes entities.
 *
 * @ingroup booking_system
 */
interface BookingEquipesInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Booking equipes name.
   *
   * @return string
   *   Name of the Booking equipes.
   */
  public function getName();

  /**
   * Sets the Booking equipes name.
   *
   * @param string $name
   *   The Booking equipes name.
   *
   * @return \Drupal\booking_system\Entity\BookingEquipesInterface
   *   The called Booking equipes entity.
   */
  public function setName($name);

  /**
   * Gets the Booking equipes creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Booking equipes.
   */
  public function getCreatedTime();

  /**
   * Sets the Booking equipes creation timestamp.
   *
   * @param int $timestamp
   *   The Booking equipes creation timestamp.
   *
   * @return \Drupal\booking_system\Entity\BookingEquipesInterface
   *   The called Booking equipes entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Booking equipes revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Booking equipes revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\booking_system\Entity\BookingEquipesInterface
   *   The called Booking equipes entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Booking equipes revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Booking equipes revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\booking_system\Entity\BookingEquipesInterface
   *   The called Booking equipes entity.
   */
  public function setRevisionUserId($uid);

}
