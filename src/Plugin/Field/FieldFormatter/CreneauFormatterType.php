<?php

namespace Drupal\booking_system\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\booking_system\Entity\BookingEquipes;

/**
 * Plugin implementation of the 'creneau_formatter_type' formatter.
 *
 * @FieldFormatter(
 *   id = "creneau_formatter",
 *   label = @Translation("Creneau formatter type"),
 *   field_types = {
 *     "creneau"
 *   }
 * )
 */
class CreneauFormatterType extends FormatterBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [ // Implement default settings.
    ] + parent::defaultSettings();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [ // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    
    return $summary;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    
    foreach ($items as $delta => $item) {
      $dateStart = new DrupalDateTime($item->date_start);
      $bg = explode(":", $item->hour_start);
      $h = (int) $bg[0];
      $i = (int) $bg[1];
      $dateStart->setTime($h, $i);
      $date_end = new DrupalDateTime($item->date_end);
      $ed = explode(":", $item->hour_end);
      $h = (int) $ed[0];
      $i = (int) $ed[1];
      $date_end->setTime($h, $i);
      $equipe = BookingEquipes::load($item->equipe);
      $elements[$delta] = [
        '#date_start' => $dateStart->format("H:i d-m-Y"),
        '#date_end' => $date_end->format("H:i d-m-Y"),
        '#equipe' => $equipe ? $equipe->label() : '',
        '#theme' => 'booking_system_creneau'
      ];
    }
    
    return $elements;
  }
  
  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *        One field item.
   *        
   * @return string The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }
  
}
