<?php

namespace Drupal\booking_system\Services\BookingManager;

/**
 * --
 *
 * @author stephane
 *        
 */
interface ManagerDateInterface {
  
  /**
   * Permet de recuperer la configuration de base afin d'afficher la premiere
   * etape de la reservation.
   *
   * @param string $booking_config_type_id
   */
  public function loadBookingConfig(string $booking_config_type_id);
  
  /**
   * Permet de renvoyer à l'app front les champs donc il a besoin.
   *
   * @param string $booking_config_type_id
   * @return []
   */
  public function mappingBookingConfigType(string $booking_config_type_id);
  
  /**
   * Ce champs est remplie si l'utilisateur n'a pas acces au creneaux.
   * (example il n'a peut plus reserver de nouveau creneaux).
   *
   * @param array $results
   * @param boolean $status
   * @return []
   */
  public function checkAccess(array &$results, bool $status = true);
  
}