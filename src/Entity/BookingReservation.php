<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\UserInterface;

/**
 * Permet de sauvegarder les données utilisateurs.
 *
 * @ingroup booking_system
 *
 * @ContentEntityType(
 *   id = "booking_reservation",
 *   label = @Translation("Booking reservation"),
 *   handlers = {
 *     "storage" = "Drupal\booking_system\BookingReservationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\booking_system\BookingReservationListBuilder",
 *     "views_data" = "Drupal\booking_system\Entity\BookingReservationViewsData",
 *     "translation" = "Drupal\booking_system\BookingReservationTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\booking_system\Form\BookingReservationForm",
 *       "add" = "Drupal\booking_system\Form\BookingReservationForm",
 *       "edit" = "Drupal\booking_system\Form\BookingReservationForm",
 *       "delete" = "Drupal\booking_system\Form\BookingReservationDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\booking_system\BookingReservationHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\booking_system\BookingReservationAccessControlHandler",
 *   },
 *   base_table = "booking_reservation",
 *   data_table = "booking_reservation_field_data",
 *   revision_table = "booking_reservation_revision",
 *   revision_data_table = "booking_reservation_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer booking reservation entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/booking_reservation/{booking_reservation}",
 *     "add-form" = "/admin/structure/booking_reservation/add",
 *     "edit-form" = "/admin/structure/booking_reservation/{booking_reservation}/edit",
 *     "delete-form" = "/admin/structure/booking_reservation/{booking_reservation}/delete",
 *     "version-history" = "/admin/structure/booking_reservation/{booking_reservation}/revisions",
 *     "revision" = "/admin/structure/booking_reservation/{booking_reservation}/revisions/{booking_reservation_revision}/view",
 *     "revision_revert" = "/admin/structure/booking_reservation/{booking_reservation}/revisions/{booking_reservation_revision}/revert",
 *     "revision_delete" = "/admin/structure/booking_reservation/{booking_reservation}/revisions/{booking_reservation_revision}/delete",
 *     "translation_revert" = "/admin/structure/booking_reservation/{booking_reservation}/revisions/{booking_reservation_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/booking_reservation",
 *   },
 *   field_ui_base_route = "booking_reservation.settings"
 * )
 */
class BookingReservation extends EditorialContentEntityBase implements BookingReservationInterface {
  
  use EntityChangedTrait;
  use EntityPublishedTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    
    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    
    return $uri_route_parameters;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    
    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);
      
      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
    
    // If no revision author has been set explicitly,
    // make the booking_reservation owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
  /**
   * Permet de determiner de comparer les creneaux.
   *
   * @return true //true si la valeur est existe
   */
  public function creneauExit(array $crenauCheck) {
    $status = false;
    $creneaux = $this->get('creneaux')->getValue();
    foreach ($creneaux as $creneau) {
      $status = true;
      if ($crenauCheck['hour_start'] !== $creneau['hour_start']) {
        $status = false;
        continue;
      }
      if ($crenauCheck['hour_end'] !== $creneau['hour_end']) {
        $status = false;
        continue;
      }
      if ($crenauCheck['equipe'] !== $creneau['equipe']['id']) {
        $status = false;
        continue;
      }
    }
    return $status;
  }
  
  /**
   * Retourne les creneaux dans un format comprehensive par l'homme.
   */
  public function getCreneauxReatable() {
    $creneaux = $this->get('creneaux')->getValue();
    $creneauxRead = [];
    if ($creneaux) {
      foreach ($creneaux as $value) {
        $dateStart = new DrupalDateTime($value['date_start']);
        $bg = explode(":", $value['hour_start']);
        $h = (int) $bg[0];
        $i = (int) $bg[1];
        $dateStart->setTime($h, $i);
        $date_end = new DrupalDateTime($value['date_end']);
        $ed = explode(":", $value['hour_end']);
        $h = (int) $ed[0];
        $i = (int) $ed[1];
        $date_end->setTime($h, $i);
        $labelEquipe = '';
        if (is_array($value['equipe'])) {
          $labelEquipe = $value['equipe']['name'];
        }
        else {
          $equipe = BookingEquipes::load($value['equipe']);
          $labelEquipe = $equipe->label();
        }
        $date = new DrupalDateTime($value['date_end']);
        $creneauxRead[] = [
          '#date_start' => $dateStart->format("H:i d-m-Y"),
          '#date_end' => $date_end->format("H:i d-m-Y"),
          '#equipe' => $labelEquipe,
          '#creneau' => $value['hour_start'] . ' - ' . $value['hour_end'],
          '#date' => $date->format("d/m/Y"),
          '#theme' => 'booking_system_creneau'
        ];
      }
    }
    return $creneauxRead;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Booking reservation entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'author',
      'weight' => 0
    ])->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the Booking reservation entity.'))->setRevisionable(TRUE)->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    //
    $fields['creneaux'] = BaseFieldDefinition::create('creneau')->setLabel(t(' Creneau '))->setDisplayOptions('form', [
      'type' => 'creneau_widget',
      'weight' => 0
    ])->setDisplayOptions('view', [
      'type' => 'creneau_formatter'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setSetting('handler', 'default')->setTranslatable(false)->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    $fields['status']->setDescription(t('A boolean indicating whether the Booking reservation is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    return $fields;
  }
  
}
