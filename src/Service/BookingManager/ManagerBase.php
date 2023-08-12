<?php

namespace Drupal\booking_system\Service\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manage the booking system
 */
class ManagerBase {
  use StringTranslationTrait;
  /**
   *
   * @var AccountInterface
   */
  protected $currentUser;
  /**
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   *
   * @var BookingConfigType
   */
  protected $BookingConfigType;

  /**
   *
   * @var string
   */
  protected $booking_config_type_id;

  /**
   * Date selectionner par l'utilisateur.
   *
   * @var DrupalDateTime
   */
  protected $selecteddate;

  /**
   * Date encours, (date sur serveur).
   *
   * @var DrupalDateTime
   */
  protected $currentDate;

  /**
   * Days sort by index
   *
   * @var array
   */
  protected $daysSortIndex = NULL;

  protected function loadBookingConfigType($booking_config_type_id) {
    if (!$this->BookingConfigType) {
      $this->BookingConfigType = BookingConfigType::load($booking_config_type_id);
      if (!$this->BookingConfigType)
        BookingSystemException::exception("The entity no longer exists or you do not have access", $booking_config_type_id);
    }
    return $this->BookingConfigType;
  }

  /**
   * La date selectionner par l'utilisateur sur le front.
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function setDateSelected($date_string) {
    if (!$this->selecteddate) {
      $this->selecteddate = new DrupalDateTime($date_string);
    }
    return $this->selecteddate;
  }

  /**
   * La date encours ( date du jour).
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function getCurrentDate() {
    if (!$this->currentDate) {
      $this->selecteddate = new DrupalDateTime();
    }
    return $this->currentDate;
  }

  /**
   * Recupere la configuration en fonction de l'indice de la journée.
   *
   * @param int $indexDay
   */
  protected function getDayconfig(int $indexDay) {
    if ($this->daysSortIndex === NULL) {
      $this->loadBookingConfigType($this->booking_config_type_id);
      $values = $this->BookingConfigType->toArray();
      foreach ($values as $value) {
        $this->daysSortIndex[$value['indice']] = $value;
      }
    }
    return $this->daysSortIndex[$indexDay];
  }

}