<?php

namespace Drupal\booking_system;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\booking_system\Entity\BookingConfigInterface;

/**
 * Defines the storage handler class for Booking config entities.
 *
 * This extends the base storage class, adding required special handling for
 * Booking config entities.
 *
 * @ingroup booking_system
 */
interface BookingConfigStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Booking config revision IDs for a specific Booking config.
   *
   * @param \Drupal\booking_system\Entity\BookingConfigInterface $entity
   *   The Booking config entity.
   *
   * @return int[]
   *   Booking config revision IDs (in ascending order).
   */
  public function revisionIds(BookingConfigInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Booking config author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Booking config revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\booking_system\Entity\BookingConfigInterface $entity
   *   The Booking config entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BookingConfigInterface $entity);

  /**
   * Unsets the language for all Booking config with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
