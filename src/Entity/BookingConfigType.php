<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

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
 *     "label"
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

}
