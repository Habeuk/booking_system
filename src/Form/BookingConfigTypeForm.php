<?php

namespace Drupal\booking_system\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BookingConfigTypeForm.
 */
class BookingConfigTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $booking_config_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $booking_config_type->label(),
      '#description' => $this->t("Label for the Booking config type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $booking_config_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\booking_system\Entity\BookingConfigType::load',
      ],
      '#disabled' => !$booking_config_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $booking_config_type = $this->entity;
    $status = $booking_config_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Booking config type.', [
          '%label' => $booking_config_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Booking config type.', [
          '%label' => $booking_config_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($booking_config_type->toUrl('collection'));
  }

}
