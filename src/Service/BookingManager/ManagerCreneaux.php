<?php

namespace Drupal\booking_system\Service\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Manage the booking system
 */
class ManagerCreneaux {
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
   * @var DatesHoursDisabled
   */
  protected $DatesHoursDisabled;

  /**
   * Date selectionner par l'utilisateur.
   *
   * @var DrupalDateTime
   */
  protected $selecteddate;

  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager, DatesHoursDisabled $DatesHoursDisabled) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->DatesHoursDisabled = $DatesHoursDisabled;
  }

  public function loadCreneaux($booking_config_type_id, $date_string) {
  }

  protected function setDateSelected($date_string) {
    if ($this->selecteddate) {
      $this->selecteddate = new DrupalDateTime($date_string);
    }
    return $this->selecteddate;
  }

}