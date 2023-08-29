<?php

namespace Drupal\booking_system\Services\BookingManager;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\booking_system\Exception\BookingSystemException;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Symfony\Component\Mime\Header\MailboxHeader;
use Symfony\Component\Mime\Address;
use Drupal\booking_system\Entity\BookingReservation;

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
  private $BookingConfigType;
  
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
  
  /**
   * Contient les reservations en fonction de la date.
   *
   * @var array
   */
  protected $ReservationBydate = [];
  
  /**
   * Contient les reservations groupées par clées.
   *
   * @var array
   */
  protected $ReservationGroupByKeys = [];
  
  /**
   * Contient la configuration du RDV.
   *
   * @var array
   */
  protected $configuration = [];
  
  /**
   * Retourne la valeur par defaut de la limit de reservation.
   *
   * @var integer
   */
  protected $DefaultLimitReservation = NULL;
  /**
   * L'entité charger de la reservation.
   *
   * @var string
   */
  const ENTITY_RESERVATION = 'booking_reservation';
  
  protected function loadBookingConfigType(string $booking_config_type_id) {
    if (!$this->BookingConfigType) {
      $this->booking_config_type_id = $booking_config_type_id;
      $this->BookingConfigType = BookingConfigType::load($booking_config_type_id);
      if (!$this->BookingConfigType)
        BookingSystemException::exception("The entity no longer exists or you do not have access", $booking_config_type_id);
    }
    return $this->BookingConfigType;
  }
  
  /**
   * Retourne la configuration et verifie qu'elle est bien definit.
   *
   * @return array|string|mixed[]
   */
  protected function getConfiguration() {
    if (!$this->configuration) {
      if (!$this->BookingConfigType)
        throw BookingSystemException::exception("BookingConfigType not define");
      $configuration = $this->BookingConfigType->toArray();
      $configuration['creneau']['duration'] = (int) $configuration['creneau']['duration'];
      $configuration['creneau']['interval'] = (int) $configuration['creneau']['interval'];
      $configuration['creneau']['gap'] = (int) $configuration['creneau']['gap'];
      $configuration['limit_reservation'] = (int) $configuration['limit_reservation'];
      $this->configuration = $configuration;
      // dump($this->configuration);
    }
    return $this->configuration;
  }
  
  /**
   * Permet de surcharger la configuration.
   */
  protected function setConfiguration(array $values) {
    $this->configuration = $values;
  }
  
  /**
   */
  public function getDefaultLimitReservation() {
    if ($this->DefaultLimitReservation === NULL) {
      $this->getConfiguration();
      $this->DefaultLimitReservation = (int) $this->BookingConfigType->get('limit_reservation');
    }
    return $this->DefaultLimitReservation;
  }
  
  /**
   * Enresgistre les creneaux.
   *
   * @param string $booking_config_type_id
   * @param array $values
   * @return number
   */
  public function saveCreneaux(string $booking_config_type_id, array $values) {
    if (empty($values['name'])) {
      $values['name'] = 'default';
    }
    foreach ($values['creneaux'] as $k => $creneau) {
      if (is_array($creneau['equipe'])) {
        $values['creneaux'][$k]['equipe'] = $creneau['equipe']['id'];
      }
    }
    /**
     *
     * @var BookingReservation $BookingReservation
     */
    $BookingReservation = $this->entityTypeManager->getStorage(self::ENTITY_RESERVATION)->create($values);
    $reservation = $BookingReservation->save();
    //
    $this->prepareMailToUser($BookingReservation);
    return $reservation;
  }
  
  /**
   * Prepare le contenu du mail à envoyer à l'utilisateur.
   *
   * @param BookingReservation $reservation
   */
  protected function prepareMailToUser(BookingReservation $reservation) {
    $email = \Drupal::currentUser()->getEmail();
    $creneaux = $reservation->getCreneauxReatable();
    if ($email && $creneaux) {
      $subject = "Reservation of a slot";
      $messages = "<h2> You have booked a slot </h2>";
      if (count($creneaux) > 1) {
        $subject = "Reservation of slots";
        $messages = "<h2> You have booked slots </h2>";
      }
      /**
       *
       * @var \Drupal\Core\Render\Renderer $renderer
       */
      $renderer = \Drupal::service('renderer');
      $messages .= $renderer->renderPlain($creneaux);
      $this->sendMails($email, $subject, $messages);
    }
  }
  
  protected function sendMails(string $to, string $subject, $message, $from = null) {
    $siteInfo = ConfigDrupal::config('system.site');
    $mailSystem = ConfigDrupal::config('mailsystem.settings');
    if (!$from) {
      $from = $siteInfo['mail'];
    }
    $key = 'booking_system_mail';
    $datas = [
      'id' => $key,
      'to' => $to,
      'subject' => $subject,
      'body' => $message,
      'headers' => [
        'From' => $from,
        'Sender' => $from,
        'Return-Path' => $from
      ]
    ];
    /**
     * On initialise le chargeur de plugin de mail.
     *
     * @var \Drupal\Core\Mail\MailManager $PluginMailManger
     */
    $PluginMailManger = \Drupal::service('plugin.manager.mail');
    
    /**
     * On recupere l'instance à partir de l'id du plugin.
     *
     * @var \Drupal\Core\Mail\MailInterface $mailPlugin
     */
    $mailPlugin = $PluginMailManger->createInstance($mailSystem['defaults']['sender']);
    
    $mailbox = new MailboxHeader('From', new Address($siteInfo['mail'], $siteInfo['name']));
    $datas['headers']['From'] = $mailbox->getBodyAsString();
    $message = $mailPlugin->format($datas);
    $result = $mailPlugin->mail($message);
    if (!$result) {
      $message = t(' There was a problem sending your email notification to @email. ', array(
        '@email' => $to
      ));
      $this->getLogger('booking_system')->alert($message);
    }
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
  
  protected function getEquipes(string $booking_config_type_id) {
    if (!$this->equipes)
      $this->equipes = $this->entityTypeManager->getStorage('booking_equipes')->loadByProperties([
        'booking_config_type' => $booking_config_type_id
      ]);
    return $this->equipes;
  }
  
  /**
   * Permet d'otenir une liste qui facilite la recherche et le decompte des
   * reservations par creneaux.
   *
   * @return []
   */
  protected function getReservationGroupByKeys(DrupalDateTime $date) {
    $date_string = $date->format('Y-m-d');
    if (!isset($this->ReservationGroupByKeys[$date_string])) {
      $this->ReservationGroupByKeys[$date_string] = [];
      $BookingReservations = $this->getReservationByDate($date);
      foreach ($BookingReservations as $BookingReservation) {
        $creneaux = $BookingReservation->get('creneaux')->getValue();
        foreach ($creneaux as $creneau) {
          $id = $creneau['equipe'];
          $hd = $creneau['hour_start'];
          $hf = $creneau['hour_end'];
          // On ajoute l'id de l'utilisateur qui a effectuer la reservation afin
          // de l'empecher de reserver le meme creneau.
          $creneau['user_id'] = $BookingReservation->getOwnerId();
          $this->ReservationGroupByKeys[$date_string][$id . $hd . $hf][] = $creneau;
        }
      }
    }
    return $this->ReservationGroupByKeys[$date_string];
  }
  
  /**
   * Permet de recuperation toutes les reservations pour une date.
   *
   * @param DrupalDateTime $date
   * @return BookingReservation[]
   */
  protected function getReservationByDate(DrupalDateTime $date) {
    $date_string = $date->format('Y-m-d');
    if (!isset($this->ReservationBydate[$date_string])) {
      $this->ReservationBydate[$date_string] = [];
      /**
       *
       * @var \Drupal\Core\Entity\Query\Sql\Query $query
       */
      $query = $this->entityTypeManager->getStorage(self::ENTITY_RESERVATION)->getQuery();
      $query->condition('creneaux.date_start', [
        $date_string
      ]);
      // dump($query->__toString());
      $ids = $query->execute();
      if ($ids) {
        $this->ReservationBydate[$date_string] = $this->entityTypeManager->getStorage(self::ENTITY_RESERVATION)->loadMultiple($ids);
      }
    }
    return $this->ReservationBydate[$date_string];
  }
  
  /**
   * Retourne la liste des equipes en function de l'id de configuration.
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
        'value' => $indice, // Value => represente l'index dans le tableaux de
                             // moniteurs.(front)
        'id' => (int) $equipe->id()
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


