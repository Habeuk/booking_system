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
class ManagerCreneaux extends ManagerBase {
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

  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager, DatesHoursDisabled $DatesHoursDisabled) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->DatesHoursDisabled = $DatesHoursDisabled;
  }

  /**
   *
   * @param string $booking_config_type_id
   * @param string $date_string
   */
  public function loadCreneaux($booking_config_type_id, $date_string) {
    $this->booking_config_type_id = $booking_config_type_id;
    $this->loadBookingConfigType($booking_config_type_id);
    return $this->buildCreneaux($date_string);
  }

  /**
   * Permet de retourner les jours desactivées en function de l'indice.
   * ( par example Samedi et Dimanche sont generalement desactive ).
   */
  public function getDisableDayByIndice($booking_config_type_id) {
    $this->loadBookingConfigType($booking_config_type_id);
    $values = $this->BookingConfigType->toArray();
    $days = $values['days'];
    $disabledDays = [];
    foreach ($days as $day) {
      if (!$day['status'])
        $disabledDays[] = $day['indice'];
    }
    return $disabledDays;
  }

  /**
   * On construit les creneaux de la journée independament de l'heure.
   */
  protected function buildCreneaux($date_string) {
    $this->setDateSelected($date_string);
    // On determine l'indice du jours.
    $indexDay = $this->selecteddate->format("w");
    $dayconf = $this->getDayconfig($indexDay);
    $values = $this->BookingConfigType->toArray();
    $creneaux = [];
    foreach ($dayconf['periodes'] as $p => $periode) {
      $creneaux[$p] = [
        'name' => $periode['label'],
        'status' => $periode['status'],
        'times' => []
      ];
      if ($periode['status']) {
        $periode += $values['creneau'];
        $this->genereateCreneauForPeriode($creneaux[$p]['times'], $periode);
      }
    }
    return $creneaux;
  }

  /**
   * Genere les creneaux pour une periode.
   *
   * @param array $times
   * @param string $date_string
   * @param array $creneauConfig
   */
  protected function genereateCreneauForPeriode(array &$times, array $creneauConfig) {
    $duration = $creneauConfig['duration'];
    $interval = $creneauConfig['interval'];
    $gap = $creneauConfig['gap'];
    /**
     * Use "gap" to determiner de the next creneaux.
     *
     * @var \Drupal\Core\Datetime\DrupalDateTime $hourBegin
     */
    $hourBegin = $this->selecteddate;
    $hourBegin->setTime($creneauConfig['h_d'], $creneauConfig['m_d']);

    $hourEnd = $this->selecteddate;
    $hourEnd->setTime($creneauConfig['h_f'], $creneauConfig['m_f']);
    // 100 creneaux par periode.
    $maxCreneauxIds = 100;
    $i = 0;
    while ($i < $maxCreneauxIds || $hourBegin > $maxCreneauxIds) {
      $times[$i] = [
        'monitors' => []
      ];
      $hourAddduration = $hourBegin;
      $hourAddduration->modify("+ " . $duration . " minutes");
      $times[$i]['hour'] = [
        'start' => $hourBegin->format("h:m"),
        'end' => $hourAddduration->format("h:m")
      ];
      $times[$i]['status'] = $this->checkIfCreneauIsActif($hourBegin, $times[$i]['hour'], $gap);
      // Pass to next creneau
      $hourBegin->modify("+ " . $interval . " minutes");
      // i++
      $i++;
    }
  }

  /**
   * - Verification des sauvagardes.
   * - Verification en fonction de l'heure encours.
   * - Verification en fonction du gap.
   */
  protected function checkIfCreneauIsActif(DrupalDateTime $hourBegin, array $hour, $gap) {
    $currentDate = $this->getCurrentDate();
    // Verification en function du temps.
    if ($gap > 0) {
      // on ne modifie pas directement l'object $hourBegin, car cela va impacter
      // la suite du processus.
      $temp = $hourBegin;
      $temp->modify("+ " . $gap . " minutes");
      if ($currentDate > $temp) {
        return false;
      }
    }
    elseif ($currentDate > $hourBegin) {
      return false;
    }
    // Verification en function des données sauvegardées.
    // //
    return true;
  }

}