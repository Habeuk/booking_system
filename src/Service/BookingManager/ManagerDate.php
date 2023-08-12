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
class ManagerDate extends ManagerBase {

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

  /**
   *
   * @var ManagerCreneaux
   */
  protected $ManagerCreneaux;

  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager, DatesHoursDisabled $DatesHoursDisabled, ManagerCreneaux $ManagerCreneaux) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->DatesHoursDisabled = $DatesHoursDisabled;
    $this->ManagerCreneaux = $ManagerCreneaux;
  }

  /**
   * Permet de recuperer la configuration de base afin d'afficher la premiere
   * etape de la reservation.
   */
  public function loadBookingConfig($booking_config_type_id) {
    return $this->mappingBookingConfigType($booking_config_type_id);
  }

  /**
   * Permet de renvoyer à l'app front les champs donc il a besoin.
   *
   * @param string $booking_config_type_id
   */
  protected function mappingBookingConfigType($booking_config_type_id) {
    $this->loadBookingConfigType($booking_config_type_id);
    $values = $this->BookingConfigType->toArray();
    $results = [];
    $results['booking_config_type_id'] = $booking_config_type_id;
    $results['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $results['date_display_mode'] = $values['date_display_mode'];
    $results['show_end_hour'] = $values['creneau']['show_end_hour'];
    $results['maintenance'] = $values['maintenance'];
    if (!empty($values['maintenance']))
      $results['maintenance_message'] = $values['maintenance_message'];
    if ($values['date_display_mode'] == 'month') {
      $this->completeBaseConfigForMonth($booking_config_type_id, $values, $results);
    }
    elseif ($values['date_display_mode'] == 'week') {
      $this->completeBaseConfigForWeek($values, $results);
    }

    /**
     * limit_reservation peut varie en fonction d'autre paramettre.
     * ( est sur l'etape suivante ).
     */
    // $results['limit_reservation'] = $values['limit_reservation'];
    return $results;
  }

  /**
   * Complete les informations de base relative à l'affichage par mois.
   */
  protected function completeBaseConfigForMonth(string $booking_config_type_id, array $values, array &$results) {
    $currentDate = new DrupalDateTime();
    $results['number_month'] = $values['number_month'];
    /**
     * Recupere l'indice du moins encours.
     */
    $results['current_month'] = $currentDate->format('m');
    $results['date_begin'] = $currentDate->format('Y-m-d');
    /**
     * Determine la date de fin.
     * ( pour la date de fin on l'a determine de number_month )
     */
    $number_month = $values['number_month'];
    if (!$number_month)
      $number_month = 1;
    $currentDate->modify($number_month . ' month');
    $results['date_end'] = $currentDate->format('Y-m-d');
    /**
     * Ajout des dates et periode de date à desactivées.
     */
    $this->DatesHoursDisabled->getDisabledDatesAndPeriode($booking_config_type_id, $results);
    /**
     * Ajout des jours desactivées en function des indices de la semaine.
     */
    $results['disabled_days'] = $this->ManagerCreneaux->getDisableDayByIndice($booking_config_type_id);
  /**
   * Ajout des jours desactivées en function de la disponibilite des creneaux.
   */
  }

  /**
   * Complete les informations de base relative à l'affichage par semaine.
   */
  protected function completeBaseConfigForWeek(array $values, array &$results) {
    $results['number_week'] = $values['number_week'];
  }

}