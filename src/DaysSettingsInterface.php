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
      'indice' => 1,
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
    ],
    [
      'label' => 'Mardi',
      'status' => true, // status pour le jour
      'indice' => 2,
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
    ],
    [
      'label' => 'Mercredi',
      'status' => true, // status pour le jour
      'indice' => 3,
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
    ],
    [
      'label' => 'Jeudi',
      'status' => true, // status pour le jour
      'indice' => 4,
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
    ],
    [
      'label' => 'Vendredi',
      'status' => true, // status pour le jour
      'indice' => 5,
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
    ],
    [
      'label' => 'Samedi',
      'status' => false, // status pour le jour
      'indice' => 6,
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
    ],
    [
      'label' => 'Dimanche',
      'status' => false, // status pour le jour
      'indice' => 0,
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