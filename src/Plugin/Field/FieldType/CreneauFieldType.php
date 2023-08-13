<?php

namespace Drupal\booking_system\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Plugin implementation of the 'creneau_field_type' field type.
 *
 * @FieldType(
 *   id = "creneau",
 *   label = @Translation("Creneau field type"),
 *   description = @Translation("Allows you to save the data of a slot"),
 *   default_widget = "creneau_widget",
 *   default_formatter = "creneau_formatter"
 * )
 */
class CreneauFieldType extends FieldItemBase {

  /**
   *
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [] + parent::defaultStorageSettings();
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['hour_start'] = DataDefinition::create('string')->setLabel(new TranslatableMarkup('Start hour'))->setRequired(TRUE);
    $properties['hour_end'] = DataDefinition::create('string')->setLabel(new TranslatableMarkup('End hour'))->setRequired(TRUE);
    $properties['date_start'] = DataDefinition::create('string')->setLabel(new TranslatableMarkup('Date hour'))->setRequired(TRUE);
    $properties['date_end'] = DataDefinition::create('string')->setLabel(new TranslatableMarkup('Date hour'))->setRequired(TRUE);
    $properties['equipe'] = DataDefinition::create('integer')->setLabel(new TranslatableMarkup('Equipe'));
    return $properties;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'hour_start' => [
          'type' => 'varchar', // format => hh:mm:ss
          'length' => 10,
          'unsigned' => FALSE,
          'not null' => TRUE
        ],
        'hour_end' => [
          'type' => 'varchar', // format => hh:mm:ss
          'length' => 10,
          'unsigned' => FALSE,
          'not null' => TRUE
        ],
        'date_start' => [ // format long => 2020-04-30T00:00:00.000
          'description' => 'The date value.',
          'type' => 'varchar',
          'length' => 25,
          'unsigned' => FALSE,
          'not null' => TRUE
        ],
        'date_end' => [
          'description' => 'The date value.',
          'type' => 'varchar',
          'length' => 25,
          'unsigned' => FALSE,
          'not null' => TRUE
        ],
        'equipe' => [
          'type' => 'int',
          'unsigned' => FALSE,
          'size' => 'normal',
          'not null' => FALSE
        ]
      ]
    ];

    return $schema;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function isEmpty() {
    $hourStart = empty($this->get('hour_start')->getValue()) ? TRUE : FALSE;
    $hourEnd = empty($this->get('hour_end')->getValue()) ? TRUE : FALSE;
    $dateStart = empty($this->get('date_start')->getValue()) ? TRUE : FALSE;
    $dateEnd = empty($this->get('date_end')->getValue()) ? TRUE : FALSE;

    return $hourStart && $hourEnd && $dateStart && $dateEnd;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $date = new DrupalDateTime();
    $values['hour_start'] = $date->format("h:i");
    $values['date_start'] = $date->format("Y-m-d h:m");
    //
    $DateEnd = $date;
    $mm = rand(60, 180);
    $DateEnd->modify("+ " . $mm);
    $values['hour_end'] = $DateEnd->format("h:i");
    $values['date_end'] = $DateEnd->format("Y-m-d h:m");
    $values['equipe'] = 0;
    return $values;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    return $elements;
  }

}
