<?php

namespace Drupal\booking_system_schedule\Services;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Services\BookingManager\ManagerCreneaux;

/**
 * Returns responses for booking_system routes.
 */
class ManageSchedule {
  
  /**
   *
   * @var ManagerCreneaux
   */
  protected $ManagerCreneaux;
  
  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager, ManagerCreneaux $ManagerCreneaux) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->ManagerCreneaux = $ManagerCreneaux;
  }
  
  /**
   * Permet de charger les creneaux d'une periode en function de la
   * configuration.
   *
   * @param string $booking_config_type_id
   * @param string $date_begin
   * @param string $date_end
   */
  function loadCreneaux(string $booking_config_type_id, string $date_begin, string $date_end) {
  }
  
  //
}