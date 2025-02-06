<?php

namespace Drupal\booking_system;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class BookingConfigStorage extends SqlContentEntityStorage implements BookingConfigStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BookingConfigInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {booking_config_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {booking_config_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BookingConfigInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {booking_config_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('booking_config_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
