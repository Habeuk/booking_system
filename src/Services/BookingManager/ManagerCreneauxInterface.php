<?php

namespace Drupal\booking_system\Services\BookingManager;

/**
 * --
 *
 * @author stephane
 *        
 */
interface ManagerCreneauxInterface {
  
  /**
   * Permet de retouner le nombre de reservation.
   *
   * @param array $configs
   * @return int
   */
  public function getLimitReservation(array $values);
  
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