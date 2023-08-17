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
    // si on a desactivé tous les jours de la semaine, error
    if (count($disabledDays) >= 7)
      throw BookingSystemException::exception("Tous les jours de la semaine sont desactivées");
    return $disabledDays;
  }

  /**
   * On construit les creneaux de la journée independament de l'heure.
   */
  protected function buildCreneaux($date_string) {
    $this->setDateSelected($date_string);
    // On determine l'indice du jours.
    $indexDay = $this->getDateSelected()->format("w");
    $dayconf = $this->getDayconfig($indexDay);
    $values = $this->BookingConfigType->toArray();
    // dump($values);
    $datas['creneau_config'] = $values['creneau'];
    /**
     * Doit être caluler en function d'autres paramettres.
     */
    $datas['creneau_config']['limit_reservation'] = $values['limit_reservation'];
    $datas['monitor_list'] = $this->getEquipesOptions($this->booking_config_type_id);
    $creneaux = [];
    foreach ($dayconf['periodes'] as $p => $periode) {
      $creneaux[$p] = [
        'name' => $periode['label'],
        'status' => $periode['status'],
        'gap' => 0,
        'horaires' => [
          'begin' => $periode['h_d'] . ':' . $periode['m_d'],
          'end' => $periode['h_f'] . ':' . $periode['m_f']
        ],
        'times' => []
      ];
      if ($periode['status']) {
        // Merge default config with override config.
        $periode += $values['creneau'];
        $creneaux[$p]['gap'] = $periode['gap'];
        $this->genereateCreneauForPeriode($creneaux[$p]['times'], $periode);
      }
    }
    $datas['schedules_list'] = $creneaux;
    return $datas;
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
    // dump($this->getDateSelected());
    /**
     * Use "gap" to determiner de the next creneaux.
     *
     * @var \Drupal\Core\Datetime\DrupalDateTime $hourBegin
     */
    $hourBegin = $this->getDateSelected();
    $hourBegin->setTime($creneauConfig['h_d'], $creneauConfig['m_d'], 0);
    // dump($hourBegin->format("Y-m-d H:i"));
    // dump($this->getDateSelected());
    //
    $hourEnd = $this->getDateSelected();
    $hourEnd->setTime($creneauConfig['h_f'], $creneauConfig['m_f'], 0);
    // dump($hourEnd->format("Y-m-d H:i"), ' /// ');
    // dd($this->getDateSelected());

    // 100 creneaux par periode.
    $maxCreneauxIds = 20;
    $i = 0;
    while ($i < $maxCreneauxIds && $hourBegin < $hourEnd) {
      $hourAddduration = $this->getNewInstanceDate($hourBegin);

      $hourAddduration->modify("+ " . $duration . " minutes");
      $times[$i]['gap'] = $gap;
      $times[$i]['active'] = false;
      $times[$i]['hour'] = [
        'start' => $hourBegin->format("H:i"),
        'end' => $hourAddduration->format("H:i")
      ];
      $monitors = $this->getEquipesAvailableByCreneau($hourBegin, $times[$i]['hour']);
      $times[$i]['monitors'] = $monitors;
      // S'il ny'a pas d'equipe disponible pour ce creneau, on le garde
      // desactivé.
      if ($monitors) {
        $times[$i]['active'] = $this->checkIfCreneauIsActif($hourBegin, $times[$i]['hour'], $gap);
      }
      // Pass to next creneau
      $hourBegin->modify("+ " . $interval . " minutes");
      // i++
      $i++;
    }
  }

  /**
   * Permet de terminer si la date a au moins un creneau actif.
   *
   * @param DrupalDateTime $Date
   * @return boolean
   */
  protected function checkIfDateHasCreneaux(DrupalDateTime $Date) {
    //
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
      $temp = $this->getNewInstanceDate($hourBegin);
      $temp->modify("- " . $gap . " minutes");
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