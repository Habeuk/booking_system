<?php

namespace Drupal\booking_system\Services\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Permet de gerer l'affichage du calendrier ou des jours à afficher.
 */
class ManagerDate extends ManagerBase implements ManagerDateInterface {
  
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
  /**
   *
   * @var integer
   */
  private $protectRecusive = 0;
  
  public function __construct(AccountInterface $currentUser, EntityTypeManager $entityTypeManager, DatesHoursDisabled $DatesHoursDisabled, ManagerCreneaux $ManagerCreneaux) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->DatesHoursDisabled = $DatesHoursDisabled;
    $this->ManagerCreneaux = $ManagerCreneaux;
  }
  
  /**
   *
   *   @inheritdoc
   */
  public function loadBookingConfig($booking_config_type_id) {
    return $this->mappingBookingConfigType($booking_config_type_id);
  }
  
  /**
   *
   * @inheritdoc
   */
  public function mappingBookingConfigType($booking_config_type_id) {
    $this->loadBookingConfigType($booking_config_type_id);
    $values = $this->getConfiguration();
    $results = [];
    $results['booking_config_type_id'] = $booking_config_type_id;
    $results['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $results['date_display_mode'] = $values['date_display_mode'];
    $results['show_end_hour'] = $values['creneau']['show_end_hour'];
    $results['maintenance'] = $values['maintenance'];
    if (!empty($values['maintenance'])) {
      $results['maintenance_message'] = $values['maintenance_message'];
      return $results;
    }
    $this->checkAccess($results);
    if (!$results['access'])
      return $results;
    
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
    /**
     * On verifie qu'il ya des creneaux pour la date encours, si non, on
     * determine la prochaine date valide.
     * RQ :
     * 1 :
     * On doit verifier que cette date n'est pas dans le tableau des dates
     * desactivées.
     * 2 :
     * Cette validation se fait uniquement sur la date du jours encours.(Pour
     * les prochaines dates, on doit juste determiner s'il ya des sauvegardes,
     * si oui et s'il nya plus de creneaux valide, on desactive la date.)
     */
    $this->protectRecusive = 0;
    $this->getValidDate($currentDate, $results);
    $results['current_month'] = $currentDate->format('m');
    $results['date_begin'] = $currentDate->format('Y-m-d');
    /**
     * Determine la date de fin.
     * ( pour la date de fin on l'a determine de number_month )
     */
    $number_month = (int) $values['number_month'];
    if ($number_month > 0)
      $number_month = 1;
    $dateEnd = $this->getNewInstanceDate($currentDate);
    $dateEnd->modify('+' . $number_month . ' month');
    $this->protectRecusive = 0;
    // dump("....................////////////////..............................");
    // dump($dateEnd);
    // si la date de fin tombe sur un jour desactivées, alors on recherche le
    // prochain jour disponible.
    $this->getValidDate($dateEnd, $results);
    $results['date_end'] = $dateEnd->format('Y-m-d');
  }
  
  /**
   * Retourne la date valid.- i.e une date qui n'est pas deja desactivé, cela
   * permet à l'application front de mieux demarer, par example on ne demarre
   * pas sur un mois donc toutes les dates sont deja desactivées.
   *
   * @param DrupalDateTime $date
   * @param array $disabled_days
   * @return DrupalDateTime
   */
  protected function getValidDate(DrupalDateTime &$date, array $results) {
    $this->protectRecusive++;
    if ($this->protectRecusive > 30) {
      throw BookingSystemException::exception("Erreur d'execution recusive fonction : getValidDate");
    }
    
    // Verification des jours "disabled_days".
    if ($results['disabled_days']) {
      $day_indice = $date->format("w");
      if (in_array($day_indice, $results['disabled_days'])) {
        // dump('Day indice : ' . $day_indice, $results['disabled_days'],
        // $date);
        // next day
        $date->modify("+1 day");
        $this->getValidDate($date, $results);
      }
    }
    // Verification dans "disabled_dates".
    if ($results['disabled_dates']) {
      $date_string = $date->format('Y-m-d');
      if (in_array($date_string, $results['disabled_dates'])) {
        // dump('Date : ' . $date_string, $results['disabled_dates'], $date);
        // next day
        $date->modify("+1 day");
        $this->getValidDate($date, $results);
      }
    }
    // Verification dans "disabled_dates_periode"
    if ($results['disabled_dates_periode']) {
      $isInclude = false;
      $currentPeriode = [];
      foreach ($results['disabled_dates_periode'] as $dates_periode) {
        $begin = new DrupalDateTime($dates_periode['value']);
        $end = new DrupalDateTime($dates_periode['end_value']);
        if ($begin <= $date && $date <= $end) {
          $isInclude = true;
          $currentPeriode = $dates_periode;
          // dump("IsInclude : " . $begin->format('Y-m-d') . ' ' .
          // $date->format('Y-m-d') . ' ' . $end->format('Y-m-d'));
          break;
        }
      }
      if ($isInclude && $currentPeriode['end_value']) {
        $datArr = explode("-", $currentPeriode['end_value']);
        $date->setDate($datArr[0], $datArr[1], $datArr[2]);
        $date->modify("+1 day");
        // dump('Periode end : ' . $currentPeriode['end_value'],
        // $results['disabled_dates_periode'], $date);
        $this->getValidDate($date, $results);
      }
    }
    /**
     * Verification via les creneaux.
     * Cette verification est consommatrice consommatrice.
     */
    $status = $this->ManagerCreneaux->checkDayHasCreneauxValide($date);
    if (!$status) {
      $date->modify("+1 day");
      $this->getValidDate($date, $results);
    }
  }
  
  /**
   * Complete les informations de base relative à l'affichage par semaine.
   */
  protected function completeBaseConfigForWeek(array $values, array &$results) {
    $results['number_week'] = $values['number_week'];
  }
  
}