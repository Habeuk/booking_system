<?php

namespace Drupal\booking_system\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\NestedArray;

/**
 * Class BookingConfigTypeForm.
 */
class BookingConfigTypeForm extends EntityForm {
  
  /**
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /**
     *
     * @var \Drupal\booking_system\Entity\BookingConfigType $booking_config_type
     */
    $booking_config_type = $this->entity;
    // dump($booking_config_type->toArray());
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $booking_config_type->label(),
      '#description' => $this->t("Label for the Booking config type."),
      '#required' => TRUE
    ];
    
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $booking_config_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\booking_system\Entity\BookingConfigType::load'
      ],
      '#disabled' => !$booking_config_type->isNew()
    ];
    /* You will need additional form elements for your custom properties. */
    $config = $booking_config_type->get('days');
    // dump($config);
    $jours = \Drupal\booking_system\DaysSettingsInterface::DAYS;
    if ($config) {
      // Ajouter ce qui manque à la configuration encours.
      $jours = NestedArray::mergeDeepArray([
        $jours,
        $config
      ], true);
    }
    
    $form['days'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuration des dates',
      '#tree' => TRUE
    ];
    // Boucles pour l'affichage des élemntes des jours
    foreach ($jours as $i => $val) {
      $status = !empty($val['status']) ? 'Actif' : 'Désactivé';
      $form['days'][$i] = [
        "#type" => 'details',
        '#title' => $val['label'] . ' : ' . $status,
        '#open' => false
      ];
      // label
      $form['days'][$i]['label'] = [
        "#type" => 'textfield',
        '#title' => 'Label : ',
        '#default_value' => isset($config['days'][$i]['label']) ? $config['days'][$i]['label'] : $val['label']
      ];
      // status
      $form['days'][$i]['status'] = [
        "#type" => 'checkbox',
        '#title' => 'Status',
        '#default_value' => isset($config['days'][$i]['status']) ? $config['days'][$i]['status'] : $val['status']
      ];
      // periodes
      $form['days'][$i]['periodes'] = [
        '#type' => 'fieldset',
        '#title' => 'Configuration des périodes',
        '#tree' => TRUE
      ];
      // boucles pour les periodes
      $periods = $jours[$i]['periodes'];
      // dump($periods);
      foreach ($periods as $j => $period) {
        // dropdown activation
        $form['days'][$i]['periodes'][$j] = [
          "#type" => 'details',
          '#title' => $period['label'],
          '#open' => false
        ];
        // label
        $form['days'][$i]['periodes'][$j]['label'] = [
          "#type" => 'textfield',
          '#title' => 'Label',
          '#default_value' => isset($config['days'][$i]['periodes'][$j]['label']) ? $config['days'][$i]['periodes'][$j]['label'] : $period['label']
        ];
        // status
        $form['days'][$i]['periodes'][$j]['status'] = [
          "#type" => 'checkbox',
          '#title' => 'Status',
          '#default_value' => isset($config['days'][$i]['periodes'][$j]['status']) ? $config['days'][$i]['periodes'][$j]['status'] : $period['status']
        ];
        // heure de but
        $form['days'][$i]['periodes'][$j]['h_d__m_d'] = [
          "#type" => 'textfield',
          '#title' => 'Heure debut',
          '#default_value' => $period['h_d'] . ':' . $period['m_d']
        ];
        // heure de but
        $form['days'][$i]['periodes'][$j]['h_f__m_f'] = [
          "#type" => 'textfield',
          '#title' => 'Heure de fin',
          '#default_value' => $period['h_f'] . ':' . $period['m_f']
        ];
      }
    }
    //
    $form['limit_reservation'] = [
      "#type" => 'number',
      '#title' => 'Nombre de reservation par creneau',
      '#default_value' => $booking_config_type->get('limit_reservation')
    ];
    //
    $form['date_display_mode'] = [
      "#type" => 'select',
      '#title' => "Mode d'affichage de la date",
      '#options' => [
        'month' => 'Afficher par mois',
        'week' => 'Afficher par semaine'
      ],
      '#default_value' => $booking_config_type->get('date_display_mode')
    ];
    //
    $form['number_week'] = [
      "#type" => 'number',
      '#title' => 'Nombre de semaine à afficher ',
      '#default_value' => $booking_config_type->get('number_week')
    ];
    //
    $form['number_month'] = [
      "#type" => 'number',
      '#title' => 'Nombre de mois à afficher ',
      '#default_value' => $booking_config_type->get('number_month')
    ];
    //
    $form['maintenance'] = [
      "#type" => 'checkbox',
      '#title' => 'Mode maintenance actif ',
      '#default_value' => $booking_config_type->get('maintenance')
    ];
    //
    $maintenance_message = $booking_config_type->get('maintenance_message');
    $form['maintenance_message'] = [
      "#type" => 'text_format',
      '#title' => 'Message à afficher lorsque la maintenance est active',
      '#format' => isset($maintenance_message['format']) ? $maintenance_message['format'] : 'full_html',
      '#default_value' => isset($maintenance_message['value']) ? $maintenance_message['value'] : 'Application disabled for maintenance'
    ];
    //
    $creneau = $booking_config_type->get('creneau');
    $form['creneau'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuration des creneaux',
      '#tree' => TRUE
    ];
    $form['creneau']['duration'] = [
      "#type" => 'number',
      '#title' => "Durée d'un creneau ",
      '#default_value' => isset($creneau['duration']) ? $creneau['duration'] : 60
    ];
    $form['creneau']['interval'] = [
      "#type" => 'number',
      '#title' => "Durée entre creneaux. ",
      '#default_value' => isset($creneau['interval']) ? $creneau['interval'] : 60
    ];
    $form['creneau']['gap'] = [
      "#type" => 'number',
      '#title' => "Decallage. ",
      '#default_value' => isset($creneau['gap']) ? $creneau['gap'] : 0
    ];
    $form['creneau']['show_end_hour'] = [
      "#type" => 'checkbox',
      '#title' => "Affiche l'heure de fin du creneau ",
      '#default_value' => isset($creneau['show_end_hour']) ? $creneau['show_end_hour'] : true
    ];
    return $form;
  }
  
  /**
   * Returns the action form element for the current entity form.
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    // add button to add dynamique configs
    $element['next_config'] = $element['submit'];
    $element['next_config']['#value'] = $this->t('Configuration of disabled days and times');
    $element['next_config']['#submit'][] = '::saveAndRedirectNextConfig';
    return $element;
  }
  
  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function saveAndRedirectNextConfig(array $form, FormStateInterface $form_state) {
    $booking_config_type = $this->entity;
    $status = $booking_config_type->save();
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Creation of a new configuration for making appointments'));
        break;
      
      default:
        $this->messenger()->addMessage($this->t('Update configuration for making appointments'));
    }
    
    \Drupal::request()->query->remove('destination');
    if (!empty($form_state->get('redirect_route'))) {
      $url = Url::fromRoute($form_state->get('redirect_route'), [
        'booking_config_type_id' => $form_state->get('booking_config_type_id')
      ], []);
      $form_state->setRedirectUrl($url);
    }
    elseif ($booking_config_type->isNew())
      $url = Url::fromRoute('entity.booking_config.add_form', [
        'booking_config_type' => $booking_config_type->id()
      ], []);
    else
      $url = Url::fromRoute('booking_system.config_resume', [
        'booking_config_type_id' => $booking_config_type->id()
      ], []);
    // dd($url->toString());
    $form_state->setRedirectUrl($url);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $booking_config_type = $this->entity;
    $status = $booking_config_type->save();
    
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Creation of a new configuration for making appointments'));
        break;
      
      default:
        $this->messenger()->addMessage($this->t('Update configuration for making appointments'));
    }
    \Drupal::request()->query->remove('destination');
    //
    if (!empty($form_state->get('redirect_route'))) {
      $url = Url::fromRoute($form_state->get('redirect_route'), [
        'booking_config_type_id' => $form_state->get('booking_config_type_id')
      ], []);
      $form_state->setRedirectUrl($url);
    }
    else
      $form_state->setRedirectUrl($booking_config_type->toUrl('collection'));
  }
  
}
