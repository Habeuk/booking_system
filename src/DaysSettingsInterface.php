<?php

namespace Drupal\booking_system;

// use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for days configuration.
 */
interface DaysSettingsInterface /* extends PluginInspectionInterface */
{
  const DAYS = [

    [
      'label' => 'Lundi',
      'status' => true, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Mardi',
      'status' => true, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Mercredi',
      'status' => true, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Jeudi',
      'status' => true, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Vendredi',
      'status' => true, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Samedi',
      'status' => false, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'decallage' => 5,
          'intervalle' => 15,
          'reduction' => 20,
          'creneaux' => []
        ]
      ]
    ],
    [
      'label' => 'Dimanche',
      'status' => false, // status pour le jour
      'periodes' => [
        [
          'label' => 'Plage horaire',
          'status' => true, // status pour période
          'h_d' => 07,
          'm_d' => 00,
          'h_f' => 17,
          'm_f' => 00,
          'meta_tags' => [], // peut contenir des informations utile pour
                               // d'autre application.
          'decallage' => 5, // remove
          'intervalle' => 15, // remove
          'reduction' => 20, // remove
          'creneaux' => [] // remove
        ]
      ]
    ]
  ];

}