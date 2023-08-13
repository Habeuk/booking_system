<?php

namespace Drupal\booking_system\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Plugin implementation of the 'creneau_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "creneau_widget",
 *   module = "booking_system",
 *   label = @Translation("Creneau widget type"),
 *   field_types = {
 *     "creneau_field_type"
 *   }
 * )
 */
class CreneauWidgetType extends WidgetBase {

  /**
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $date_start = isset($items[$delta]->date_start) ? DrupalDateTime::createFromTimestamp($items[$delta]->date_start) : '';
    $element['hour_start'] = [
      '#title' => $this->t('Start Hour'),
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#date_increment' => 0,
      '#default_value' => $date_start
    ];
    $element['date_start'] = [
      "#type" => 'datetime',
      '#title' => t('Start Date'),
      '#default_value' => $date_start,
      '#date_date_element' => 'date',
      '#date_time_element' => 'none',
      '#date_increment' => 0
    ];
    $date_end = isset($items[$delta]->date_end) ? DrupalDateTime::createFromTimestamp($items[$delta]->date_end) : '';
    $element['hour_end'] = [
      '#title' => $this->t('End Hour'),
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#date_increment' => 0,
      '#default_value' => $date_end
    ];
    $element['date_end'] = [
      "#type" => 'datetime',
      '#title' => t('End Date'),
      '#default_value' => $date_end,
      '#date_date_element' => 'date',
      '#date_time_element' => 'none',
      '#date_increment' => 0
    ];
    // Contient l'identifiant de l'equipe.
    $element['equipe'] = [
      '#title' => $this->t('Equipe'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->equipe) ? $items[$delta]->equipe : 0
    ];
    return $element;
  }

  /**
   *
   * {@inheritdoc}
   */
  function massageFormValues($values, $form, $form_state) {
    foreach ($values as &$value) {
      if (!empty($value)) {
        $value["hour_start"] = !empty($value["hour_start"]) ? $value["hour_start"]->format("h:i") : NULL;
        $value["hour_end"] = !empty($value["hour_end"]) ? $value["hour_end"]->format("h:i") : NULL;
        $value["date_start"] = !empty($value["date_start"]) ? $value["date_start"]->format("Y-m-d h:m") : NULL;
        $value["date_end"] = !empty($value["date_end"]) ? $value["date_end"]->format("Y-m-d h:m") : NULL;
      }
      else
        $value = [];
    }
    return $values;
  }

}
