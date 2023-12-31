<?php

namespace Drupal\booking_system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\booking_system\DaysSettingsInterface;

/**
 * Configure booking_system settings for this site.
 */
class SettingsForm extends ConfigFormBase implements DaysSettingsInterface {

  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'booking_system_settings';
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'booking_system.settings'
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('booking_system.settings')->getRawData();
    // dump($config);
    /* You will need additional form elements for your custom properties. */
    $jours = \Drupal\booking_system\DaysSettingsInterface::DAYS;
    // dump($jours);
    /*
     * if (!empty($this->config('booking_system.settings')->get('jours'))) {
     * $jours = $this->config('booking_system.settings')->get('jours');
     * }
     */
    $form['jours'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuration des dates',
      '#tree' => TRUE
    ];
    // boucles pour l'affichage des élemntes des jours
    foreach ($jours as $i => $val) {
      // dump($jours);
      // display the dropdown
      $form['jours'][$i] = [
        "#type" => 'details',
        '#title' => $val['label'],
        '#open' => false
      ];
      // label
      $form['jours'][$i]['label'] = [
        "#type" => 'textfield',
        '#title' => 'Label',
        '#default_value' => isset($config['jours'][$i]['label']) ? $config['jours'][$i]['label'] : $val['label']
      ];
      // status
      $form['jours'][$i]['status'] = [
        "#type" => 'checkbox',
        '#title' => 'Status',
        '#default_value' => isset($config['jours'][$i]['status']) ? $config['jours'][$i]['status'] : $val['status']
      ];
      // periodes
      $form['jours'][$i]['periodes'] = [
        '#type' => 'fieldset',
        '#title' => 'Configuration des périodes',
        '#tree' => TRUE
      ];
      // boucles pour les periodes
      $periods = $jours[$i]['periodes'];
      // dump($periods);
      foreach ($periods as $j => $period) {
        // dropdown activation
        $form['jours'][$i]['periodes'][$j] = [
          "#type" => 'details',
          '#title' => $period['label'],
          '#open' => false
        ];
        // label
        $form['jours'][$i]['periodes'][$j]['label'] = [
          "#type" => 'textfield',
          '#title' => 'Label',
          '#default_value' => isset($config['jours'][$i]['periodes'][$j]['label']) ? $config['jours'][$i]['periodes'][$j]['label'] : $period['label']
        ];
        // status
        $form['jours'][$i]['periodes'][$j]['status'] = [
          "#type" => 'checkbox',
          '#title' => 'Status',
          '#default_value' => isset($config['jours'][$i]['periodes'][$j]['status']) ? $config['jours'][$i]['periodes'][$j]['status'] : $period['status']
        ];
        // heure de but
        $form['jours'][$i]['periodes'][$j]['h_d__m_d'] = [
          "#type" => 'textfield',
          '#title' => 'Heure debut',
          '#default_value' => $period['h_d'] . ':' . $period['m_d']
        ];
        // heure de but
        $form['jours'][$i]['periodes'][$j]['h_f__m_f'] = [
          "#type" => 'textfield',
          '#title' => 'Heure de fin',
          '#default_value' => $period['h_f'] . ':' . $period['m_f']
        ];
        // decallage :
        $form['jours'][$i]['periodes'][$j]['decallage'] = [
          '#type' => 'number',
          '#title' => "Decallage entre les creneaux",
          '#default_value' => isset($config['jours'][$i]['periodes'][$j]['decallage']) ? $config['jours'][$i]['periodes'][$j]['decallage'] : $period['decallage']
        ];
        // intervalle :
        $form['jours'][$i]['periodes'][$j]['intervalle'] = [
          '#type' => 'number',
          '#title' => "Intervalle de temps des creneaux",
          '#default_value' => isset($config['jours'][$i]['periodes'][$j]['intervalle']) ? $config['jours'][$i]['periodes'][$j]['intervalle'] : $period['intervalle']
        ];
        // reduction de la périodes :
        $form['jours'][$i]['periodes'][$j]['reduction'] = [
          '#type' => 'number',
          '#title' => "Reduction pour la période",
          '#default_value' => isset($config['jours'][$i]['periodes'][$j]['reduction']) ? $config['jours'][$i]['periodes'][$j]['reduction'] : $period['reduction']
        ];
      }
    }
    // isset($config['jours'][$i]['status']) ? $config['jours'][$i]['status'] :
    // $val['status']
    // define the reduction
    $form['reduction'] = [
      '#type' => 'number',
      '#title' => t("Valeur de la reduction"),
      '#default_value' => isset($config['reduction']) ? $config['reduction'] : 10
    ];
    // Define number of days
    $form['number_of_days'] = [
      '#type' => 'number',
      '#title' => t("Nombre de jours à afficher"),
      '#default_value' => isset($config['number_of_days']) ? $config['number_of_days'] : 60
    ];
    // Define number of max persons
    $form['number_of_persons'] = [
      '#type' => 'number',
      '#title' => t("Nombre de personnes maximal par table"),
      '#default_value' => isset($config['number_of_persons']) ? $config['number_of_persons'] : 15
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
     * if ($form_state->getValue('example') != 'example') {
     * $form_state->setErrorByName('example', $this->t('The value is not
     * correct.'));
     * }
     */
    parent::validateForm($form, $form_state);
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('booking_system.settings');
    $this->config->set('jours', $form_state->getValue('jours'));
    $this->config->set('reduction', $form_state->getValue('reduction'));
    $this->config->set('number_of_days', $form_state->getValue('number_of_days'));
    $this->config->set('number_of_persons', $form_state->getValue('number_of_persons'));
    $this->config->save();
    parent::submitForm($form, $form_state);
  }

}
