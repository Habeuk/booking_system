<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Defines the Booking config type entity.
 * Contient la configuration de base des Reservations
 *
 * @ConfigEntityType(
 *   id = "booking_config_type",
 *   label = @Translation("Booking config type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\booking_system\BookingConfigTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\booking_system\Form\BookingConfigTypeForm",
 *       "edit" = "Drupal\booking_system\Form\BookingConfigTypeForm",
 *       "delete" = "Drupal\booking_system\Form\BookingConfigTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\booking_system\BookingConfigTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "limit_reservation",
 *     "uid",
 *     "date_display_mode",
 *     "number_week",
 *     "number_month",
 *     "days",
 *     "maintenance",
 *     "maintenance_message",
 *     "creneau"
 *   },
 *   config_prefix = "booking_config_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "booking_config",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/booking_config_type/{booking_config_type}",
 *     "add-form" = "/admin/structure/booking_config_type/add",
 *     "edit-form" = "/admin/structure/booking_config_type/{booking_config_type}/edit",
 *     "delete-form" = "/admin/structure/booking_config_type/{booking_config_type}/delete",
 *     "collection" = "/admin/structure/booking_config_type"
 *   }
 * )
 */
class BookingConfigType extends ConfigEntityBundleBase implements BookingConfigTypeInterface {
  
  /**
   * The Booking config type ID.
   *
   * @var string
   */
  protected $id;
  
  /**
   * The Booking config type label.
   *
   * @var string
   */
  protected $label;
  
  /**
   * Limitation of the number of reservations per team or per person.
   *
   * @var integer
   */
  protected $limit_reservation = 1;
  
  /**
   * user uid
   *
   * @var integer
   */
  protected $uid = 0;
  
  /**
   * Display mode
   *
   * @var string
   */
  protected $date_display_mode = 'month'; // 'month','week'
  
  /**
   * Number of weeks to display
   *
   * @var integer
   */
  protected $number_week = 3;
  
  /**
   * Number of months to display
   *
   * @var integer
   */
  protected $number_month = 2;
  
  /**
   * The days of the week.
   *
   * @var array
   */
  protected $days = [];
  
  /**
   * End date.
   * Est une valeur dynamique qui est envoyé.
   *
   * @var string
   */
  // protected $date_end = '';
  
  /**
   * maintenance mode
   *
   * @var boolean
   */
  protected $maintenance = false;
  
  /**
   * Text of maintenance mode.
   *
   * @var string
   */
  protected $maintenance_message = [
    'value' => 'Application disabled for maintenance',
    'format' => 'full_html'
  ];
  
  /**
   * config for creneau
   * creneau.duration //Durée d'un creneau [heure debut et heure de fin].
   * creneau.interval //Durée entre deux creneaux.
   * creneau.gap //Durée avant le premier creneau valide.
   * creneau.show_end_hour //Affiche l'heure d fin.
   *
   * @var array
   */
  protected $creneau = [
    'duration' => 60,
    'interval' => 60,
    'gap' => 0,
    'show_end_hour' => true
  ];
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Config\Entity\ConfigEntityBase::preSave()
   */
  public function preSave(EntityStorageInterface $storage) {
    // $uid. ( L'uid de celui qui a cree le formulaire ).
    if (!$this->uid) {
      $this->uid = \Drupal::currentUser()->id();
    }
    parent::preSave($storage);
    // merge data with the default value;
    $jours = \Drupal\booking_system\DaysSettingsInterface::DAYS;
    $days = $this->get('days');
    $final = [];
    if ($days)
      foreach ($days as $k => $day) {
        $final[$k] = $day;
        if ($jours[$k])
          $final[$k] += $jours[$k];
        foreach ($day['periodes'] as $p => $periode) {
          if (!empty($jours[$k]['periodes'][$p]))
            $final[$k]['periodes'][$p] = $jours[$k]['periodes'][$p];
          if (!empty($periode['h_d__m_d'])) {
            $h_d__m_d = explode(":", $periode['h_d__m_d']);
            $final[$k]['periodes'][$p]['h_d'] = (int) $h_d__m_d[0];
            $final[$k]['periodes'][$p]['m_d'] = (int) $h_d__m_d[1];
          }
          if (!empty($periode['h_f__m_f'])) {
            $h_f__m_f = explode(":", $periode['h_f__m_f']);
            $final[$k]['periodes'][$p]['h_f'] = (int) $h_f__m_f[0];
            $final[$k]['periodes'][$p]['m_f'] = (int) $h_f__m_f[1];
          }
        }
      }
    else {
      $final = $jours;
    }
    $this->set("days", $final);
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Config\Entity\ConfigEntityBundleBase::postSave()
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    // On effectue des validations de logique en function. Cela doit se faire au
    // niveau de la validation de l'entité.
    /**
     *
     * @var \Drupal\booking_system\Service\BookingManager\ManagerCreneaux $app_manager_creneau
     */
    $app_manager_creneau = \Drupal::service('booking_system.app_manager_creneaux');
    /**
     * Permet de s'assurer qu'il ya au moins un journée active.
     */
    $app_manager_creneau->getDisableDayByIndice($this->id);
  }
  
  /**
   * Permet de resume les informations
   */
  public function getConfigResume() {
    return [
      '#theme' => 'booking_config_type_resume',
      '#title' => $this->label,
      '#days' => $this->days,
      '#creneau' => $this->creneau,
      '#maintenance' => $this->maintenance,
      '#date_display_mode' => $this->date_display_mode
    ];
  }
  
}
