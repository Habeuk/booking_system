<?php

namespace Drupal\booking_system\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Booking config edit forms.
 *
 * @ingroup booking_system
 */
class BookingConfigForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\booking_system\Entity\BookingConfig $entity */
    $form = parent::buildForm($form, $form_state);
    $form['#title'] = $this->t('Add disabled dates and times');
    return $form;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Booking config.', [
          '%label' => $entity->label()
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Booking config.', [
          '%label' => $entity->label()
        ]));
    }
    $form_state->setRedirect('entity.booking_config.canonical', [
      'booking_config' => $entity->id()
    ]);
  }

}
