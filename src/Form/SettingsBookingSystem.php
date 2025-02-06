<?php

namespace Drupal\booking_system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsBookingSystem.
 */
class SettingsBookingSystem extends ConfigFormBase {

  /**
   * Drupal\Core\Cache\MemoryCache\MemoryCacheInterface definition.
   *
   * @var \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface
   */
  protected $entityMemoryCache;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityMemoryCache = $container->get('entity.memory_cache');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'booking_system.settingsbookingsystem'
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_booking_system';
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('booking_system.settingsbookingsystem')->getRawData();
    $form['#tree'] = true;
    $form['calandar'] = [
      '#type' => 'details',
      '#title' => $this->t('Calendar config'),
      '#open' => true
    ];
    $form['calandar']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('calendar'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['calandar']['name']) ? $config['calandar']['name'] : 'Calendar'
    ];
    $form['calandar']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('calendar'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['calandar']['title']) ? $config['calandar']['title'] : 'Please select the day of booking'
    ];
    $form['calandar']['resumed_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('resumed label'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['calandar']['resumed_label']) ? $config['calandar']['resumed_label'] : 'Date Selected'
    ];
    $form['schedule'] = [
      '#type' => 'details',
      '#title' => $this->t('Schedule config'),
      '#open' => true
    ];
    $form['schedule']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schedule'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['schedule']['name']) ? $config['schedule']['name'] : 'Schedule'
    ];
    $form['schedule']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('calendar'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['schedule']['title']) ? $config['schedule']['title'] : 'Please select the different time slots'
    ];
    $form['schedule']['resumed_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('resumed label'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['schedule']['resumed_label']) ? $config['schedule']['resumed_label'] : 'Selected slots'
    ];
    $form['summary'] = [
      '#type' => 'details',
      '#title' => $this->t('Summary'),
      '#open' => true
    ];
    $form['summary']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schedule'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['summary']['name']) ? $config['summary']['name'] : 'Schedule'
    ];
    $form['summary']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('calendar'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['summary']['title']) ? $config['summary']['title'] : 'Please select the different time slots'
    ];
    $form['summary']['parameters'] = [
      '#type' => 'details',
      '#title' => $this->t('Parameters'),
      '#open' => true
    ];
    $form['summary']['parameters']['call_to_action'] = [
      '#type' => 'textfield',
      '#title' => $this->t('call to action'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => isset($config['summary']['name']) ? $config['summary']['name'] : 'Book now'
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('booking_system.settingsbookingsystem');
    $config->set('calandar', $form_state->getValue('calandar'));
    $config->set('schedule', $form_state->getValue('schedule'));
    $config->set('summary', $form_state->getValue('summary'));
    $config->save();
  }

}
