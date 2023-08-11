<?php

namespace Drupal\booking_system;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\booking_system\Entity\BookingEquipesInterface;

/**
 * Defines the storage handler class for Booking equipes entities.
 *
 * This extends the base storage class, adding required special handling for
 * Booking equipes entities.
 *
 * @ingroup booking_system
 */
interface BookingEquipesStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Booking equipes revision IDs for a specific Booking equipes.
   *
   * @param \Drupal\booking_system\Entity\BookingEquipesInterface $entity
   *   The Booking equipes entity.
   *
   * @return int[]
   *   Booking equipes revision IDs (in ascending order).
   */
  public function revisionIds(BookingEquipesInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Booking equipes author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Booking equipes revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\booking_system\Entity\BookingEquipesInterface $entity
   *   The Booking equipes entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BookingEquipesInterface $entity);

  /**
   * Unsets the language for all Booking equipes with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
