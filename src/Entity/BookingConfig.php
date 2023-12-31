<?php

namespace Drupal\booking_system\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the Booking config entity.
 * Contient la configuration dynamiques de la reservation ( dates desactivées,
 * heures desactivées. );
 *
 * @ingroup booking_system
 *
 * @ContentEntityType(
 *   id = "booking_config",
 *   label = @Translation("Booking config"),
 *   bundle_label = @Translation("Booking config type"),
 *   handlers = {
 *     "storage" = "Drupal\booking_system\BookingConfigStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\booking_system\BookingConfigListBuilder",
 *     "views_data" = "Drupal\booking_system\Entity\BookingConfigViewsData",
 *     "translation" = "Drupal\booking_system\BookingConfigTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\booking_system\Form\BookingConfigForm",
 *       "add" = "Drupal\booking_system\Form\BookingConfigForm",
 *       "edit" = "Drupal\booking_system\Form\BookingConfigForm",
 *       "delete" = "Drupal\booking_system\Form\BookingConfigDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\booking_system\BookingConfigHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\booking_system\BookingConfigAccessControlHandler",
 *   },
 *   base_table = "booking_config",
 *   data_table = "booking_config_field_data",
 *   revision_table = "booking_config_revision",
 *   revision_data_table = "booking_config_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer booking config entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
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
 *     "canonical" = "/admin/structure/booking_config/{booking_config}",
 *     "add-page" = "/admin/structure/booking_config/add",
 *     "add-form" = "/admin/structure/booking_config/add/{booking_config_type}",
 *     "edit-form" = "/admin/structure/booking_config/{booking_config}/edit",
 *     "delete-form" = "/admin/structure/booking_config/{booking_config}/delete",
 *     "version-history" = "/admin/structure/booking_config/{booking_config}/revisions",
 *     "revision" = "/admin/structure/booking_config/{booking_config}/revisions/{booking_config_revision}/view",
 *     "revision_revert" = "/admin/structure/booking_config/{booking_config}/revisions/{booking_config_revision}/revert",
 *     "revision_delete" = "/admin/structure/booking_config/{booking_config}/revisions/{booking_config_revision}/delete",
 *     "translation_revert" = "/admin/structure/booking_config/{booking_config}/revisions/{booking_config_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/booking_config",
 *   },
 *   bundle_entity_type = "booking_config_type",
 *   field_ui_base_route = "entity.booking_config_type.edit_form"
 * )
 */
class BookingConfig extends EditorialContentEntityBase implements BookingConfigInterface {

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
    // make the booking_config owner the revision author.
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
   * Permet de recuperer l'enssemble des dates desactivées.
   */
  public function getDisabledDates() {
    $values = $this->get('disabled_dates')->getValue();
    $dates = [];
    if ($values) {
      foreach ($values as $value) {
        $dates[] = $value['value'];
      }
    }
    return $dates;
  }

  /**
   * Permet de recuperer les plages de dates à desactivées.
   */
  public function getDisabledDatesPeriode() {
    $values = $this->get('disabled_dates_periode')->getValue();
    $dates = [];
    foreach ($values as $value) {
      $dates[] = $value;
    }
    return $dates;
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
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Booking config entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
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

    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Title'))->setDescription(t('The name of the Booking config entity.'))->setRevisionable(TRUE)->setSettings([
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

    # desactive les dates
    $fields['disabled_dates'] = BaseFieldDefinition::create('datetime')->setLabel(t('Disabled dates'))->setSettings([
      'datetime_type' => 'date'
    ])->setRevisionable(TRUE)->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => 0
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setDisplayOptions('form', [
      'type' => 'dis_hours_date_time_widget',
      'weight' => 0
    ])->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    # Desactive les dates
    $fields['disabled_dates_periode'] = BaseFieldDefinition::create('daterange')->setLabel(t('Disable a date period'))->setSettings([
      'datetime_type' => 'date'
    ])->setRevisionable(TRUE)->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => 0
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setDisplayOptions('form', [
      'type' => 'dis_hours_date_time_widget',
      'weight' => 0
    ])->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    # desactive les periodes d'heures.
    $fields['disabled_hours_periode'] = BaseFieldDefinition::create('schedule')->setLabel(t('Disabled range of hours'))->setDescription(t('The range time for the offer'))->setRevisionable(TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)->setRequired(FALSE);

    $fields['status']->setDescription(t('A boolean indicating whether the Booking config is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);

    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')->setLabel(t('Revision translation affected'))->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))->setReadOnly(TRUE)->setRevisionable(TRUE)->setTranslatable(TRUE);

    return $fields;
  }

}
