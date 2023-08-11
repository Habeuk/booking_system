<?php

namespace Drupal\booking_system\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\booking_system\Entity\BookingConfigType;
use Drupal\Core\Url;

/**
 * Returns responses for booking_system routes.
 */
class BookingSystemConfigResumeController extends ControllerBase {

  /**
   * Permet d'afficher le resume d'une configuration.
   * - Afficher les dates et heures desactivés.
   * - Afficher egalement les equipes.
   */
  public function ConfigResume(Request $Request, $booking_config_type_id) {
    $BookingConfigType = BookingConfigType::load($booking_config_type_id);
    $build = [];
    if (!empty($BookingConfigType)) {
      $build['booking_config_type'] = [
        '#type' => 'details',
        '#title' => 'Configuration de base : ' . $BookingConfigType->label()
      ];
      $build['booking_config_type'][] = $BookingConfigType->getConfigResume();
      $build['booking_config_type'][] = [
        '#type' => 'link',
        '#title' => 'Edit config',
        '#url' => Url::fromRoute("entity.booking_config_type.edit_form", [
          'booking_config_type' => $BookingConfigType->id()
        ]),
        '#options' => [
          'attributes' => [
            'target' => '_blank',
            'class' => [
              'button',
              'button--primary'
            ]
          ]
        ]
      ];
      //
      $build['booking_configs'] = [
        '#type' => 'details',
        '#title' => 'Disabled dates and hours',
        '#open' => TRUE
      ];
      $build['booking_configs'][] = [
        '#type' => 'link',
        '#title' => $this->t('Add Booking config'),
        '#url' => Url::fromRoute("entity.booking_config.add_form", [
          'booking_config_type' => $BookingConfigType->id()
        ], [
          'query' => [
            'destination' => $Request->getPathInfo()
          ]
        ]),
        '#options' => [
          'attributes' => [
            'target' => '_blank',
            'class' => [
              'button',
              'button--primary'
            ]
          ]
        ]
      ];
      $query = $this->entityTypeManager()->getStorage('booking_config')->getQuery();
      $query->accessCheck(TRUE);
      $query->sort($BookingConfigType->getEntityType()->getKey('id'));
      $query->pager(100);
      $ids = $query->execute();
      /**
       *
       * @var \Drupal\booking_system\BookingConfigListBuilder $ListBuilder
       */
      $ListBuilder = $this->entityTypeManager()->getListBuilder('booking_config');
      // dump($ListBuilder);
      $ListBuilder->setCustomIds($ids);
      $build['booking_configs'][] = $ListBuilder->render();

      //
      $build['booking_equipes'] = [
        '#type' => 'details',
        '#title' => 'Equipes',
        '#open' => TRUE
      ];
    }
    else
      $this->messenger()->addWarning('This config not exist');
    //
    return $build;
  }

}