<?php

namespace Drupal\booking_system\Service\BookingManager;

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
   *
   * @var DrupalDateTime
   */
  protected $selecteddate;

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
      $this->BookingConfigType = BookingConfigType::load($booking_config_type_id);
      if (!$this->BookingConfigType)
        BookingSystemException::exception("The entity no longer exists or you do not have access", $booking_config_type_id);
    }
    return $this->BookingConfigType;
  }

  /**
   * Permet de recuperer les equipes disponible pour un creneau.
   *
   * @param DrupalDateTime $hourBegin
   * @param array $hour
   * @return array
   */
  protected function getEquipesAvailableByCreneau(DrupalDateTime $hourBegin, array $hour) {
    return [];
  }

  protected function getEquipes(string $booking_config_type_id) {
    if (!$this->equipes)
      $this->equipes = $this->entityTypeManager->getStorage('booking_equipes')->loadByProperties([
        'booking_config_type' => $booking_config_type_id
      ]);
    return $this->equipes;
  }

  /**
   * Retourne la liste des equipes en function du creneau.
   * (un creneau peu etre disponible pour une equipe et pas pour une autre).
   *
   * @param string $booking_config_type_id
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]|NULL[]
   */
  protected function getEquipesOptions(string $booking_config_type_id) {
    $this->getEquipes($booking_config_type_id);
    $options = [];
    foreach ($this->equipes as $equipe) {
      $options[$equipe->id()] = $equipe->label();
    }
    return $options;
  }

  /**
   * La date selectionner par l'utilisateur sur le front.
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function setDateSelected(string $date_string) {
    if (!$this->selecteddate) {
      $this->selecteddate = new DrupalDateTime($date_string);
    }
    return $this->selecteddate;
  }

  /**
   * La date encours ( date du jour).
   *
   * @param string $dateString
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  protected function getCurrentDate() {
    if (!$this->currentDate) {
      $this->selecteddate = new DrupalDateTime();
    }
    return $this->currentDate;
  }

  /**
   * Recupere la configuration en fonction de l'indice de la journée.
   *
   * @param int $indexDay
   */
  protected function getDayconfig(int $indexDay) {
    if ($this->daysSortIndex === NULL) {
      $this->loadBookingConfigType($this->booking_config_type_id);
      $values = $this->BookingConfigType->toArray();
      foreach ($values['days'] as $value) {
        $this->daysSortIndex[$value['indice']] = $value;
      }
    }
    return $this->daysSortIndex[$indexDay];
  }

}