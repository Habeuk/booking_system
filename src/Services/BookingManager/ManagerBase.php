<?php

namespace Drupal\booking_system\Services\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manage the booking system
 */
class ManagerBase {
  use StringTranslationTrait;
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
   * @var string
   */
  protected $booking_config_type_id;
  
  /**
   * Date selectionner par l'utilisateur.
   * Elle est privée afin qu'elle ne soit pas modifiable par d'autre fonction.
   * ( car date est un Object statique ).
   *
   * @var DrupalDateTime
   */
  private $selecteddate;
  
  /**
   * Date encours, (date sur serveur).
   *
   * @var DrupalDateTime
   */
  protected $currentDate;
  
  /**
   * Days sort by index
   *
   * @var array
   */
  protected $daysSortIndex = NULL;
  
  /**
   *
   * @var array
   */
  protected $equipes = [];
  
  protected function loadBookingConfigType(string $booking_config_type_id) {
    if (!$this->BookingConfigType) {
      $this->booking_config_type_id = $booking_config_type_id;
      $this->BookingConfigType = BookingConfigType::load($booking_config_type_id);
      if (!$this->BookingConfigType)
        BookingSystemException::exception("The entity no longer exists or you do not have access", $booking_config_type_id);
    }
    return $this->BookingConfigType;
  }
  
  public function saveCreneaux(string $booking_config_type_id, array $values) {
    $BookingReservation = \Drupal\booking_system\Entity\BookingReservation::create($values);
    return $BookingReservation->save();
  }
  
  /**
   * Ce champs est remplie si l'utilisateur n'a pas acces au creneaux.
   * (example il n'a peut plus reserver de nouveau creneaux).
   *
   * @param array $results
   * @param boolean $status
   */
  public function checkAccess(array &$results, bool $status = true) {
    $results['access'] = true;
    $results['ban_reason'] = '';
  }
  
  /**
   * Permet de recuperer les equipes disponible pour un creneau.
   *
   * @param DrupalDateTime $hourBegin
   * @param array $hour
   * @return array
   */
  protected function getEquipesAvailableByCreneau(DrupalDateTime $hourBegin, array $hour) {
    $options = [];
    if (!$this->equipes) {
      $this->getEquipes($this->booking_config_type_id);
    }
    foreach ($this->equipes as $equipe) {
      $options[] = $equipe->id();
    }
    return $options;
  }
  
  protected function getEquipes(string $booking_config_type_id) {
    if (!$this->equipes)
      $this->equipes = $this->entityTypeManager->getStorage('booking_equipes')->loadByProperties([
        'booking_config_type' => $booking_config_type_id
      ]);
    return $this->equipes;
  }
  
  /**
   * Retourne la liste des equipes en function de l'id de configuration.
   * (un creneau peu etre disponible pour une equipe et pas pour une autre).
   *
   * @param string $booking_config_type_id
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]|NULL[]
   */
  protected function getEquipesOptions(string $booking_config_type_id) {
    $this->getEquipes($booking_config_type_id);
    $options = [];
    $indice = 1;
    foreach ($this->equipes as $equipe) {
      $options[] = [
        'name' => $equipe->label(),
        'value' => $indice,
        'id' => $equipe->id()
      ];
      $indice++;
    }
    return $options;
  }
  
  /**
   * La date selectionner par l'utilisateur sur le front.
   * ( il faut faire attention pour ne pas modifier cette date ).
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function setDateSelected(string $date_string) {
    // if (!$this->selecteddate) { on masque pour l'instant car la construction
    // des dates, l'utilise et peut passer une suite de jour.
    $selecteddate = new DrupalDateTime($date_string);
    $this->selecteddate = $selecteddate->__toString();
    // }
    return $this->selecteddate;
  }
  
  /**
   * Recupere la date selectionné par l'utilisateur.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function getDateSelected() {
    if (!$this->selecteddate)
      throw BookingSystemException::exception("La date selectionner par l'utilisateur n'est pas definit");
    // return new DrupalDateTime($this->selecteddate->format("Y-m-d H:i:s"));
    return new DrupalDateTime($this->selecteddate);
  }
  
  /**
   * Les dates sont des objects statiques, donc pour modifier un object sans
   * impacter son origin il faut faire un nouveau new.
   *
   * @param DrupalDateTime $date
   */
  protected function getNewInstanceDate(DrupalDateTime $date) {
    return new DrupalDateTime($date->__toString());
  }
  
  /**
   * La date encours ( date du jour).
   * Attention On ne peut
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function getCurrentDate() {
    if (!$this->currentDate) {
      $this->currentDate = new DrupalDateTime();
    }
    return $this->currentDate;
  }
  
  /**
   * Recupere la configuration en fonction de l'indice de la journée.
   *
   * @param int $indexDay
   */
  protected function getDayconfig(int $indexDay) {
    // if ($this->daysSortIndex === NULL) { // on masque car pas utile.
    $this->loadBookingConfigType($this->booking_config_type_id);
    $values = $this->BookingConfigType->toArray();
    foreach ($values['days'] as $value) {
      $this->daysSortIndex[$value['indice']] = $value;
    }
    // }
    return $this->daysSortIndex[$indexDay];
  }
  
}


