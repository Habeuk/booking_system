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
   * Permet de retouner le nombre de reservation restant.
   * Il peut etre surcharger afin d'ajutser cette valeur en function du besoin,
   * elle ne retourne pas forcement la valeur par defaut.
   *
   * @param array $configs
   * @return int
   */
  public function getLimitReservation();
  
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