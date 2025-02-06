<?php

namespace Drupal\booking_system\Services\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Entity\BookingConfig;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Utility\NestedArray;

/**
 * Manage hours and date disabled.
 */
class DatesHoursDisabled {
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
  
  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }
  
  /**
   * Recupere les dates desactivées.
   */
  public function getDisabledDatesAndPeriode($booking_config_type_id, array &$results) {
    $bookingConfigs = $this->entityTypeManager->getStorage('booking_config')->loadByProperties([
      'type' => $booking_config_type_id
    ]);
    if (!isset($results['disabled_dates']))
      $results['disabled_dates'] = [];
    if (!isset($results['disabled_dates_periode']))
      $results['disabled_dates_periode'] = [];
    foreach ($bookingConfigs as $bookingConfig) {
      /**
       *
       * @var BookingConfig $bookingConfig
       */
      $results['disabled_dates'] = NestedArray::mergeDeepArray([
        $results['disabled_dates'],
        $bookingConfig->getDisabledDates()
      ]);
      $results['disabled_dates_periode'] = NestedArray::mergeDeepArray([
        $results['disabled_dates_periode'],
        $bookingConfig->getDisabledDatesPeriode()
      ]);
    }
  }
  
}