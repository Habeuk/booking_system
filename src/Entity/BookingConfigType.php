<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

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
  protected $number_month = 1;

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
   *
   * @var array
   */
  protected $creneau = [];

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
