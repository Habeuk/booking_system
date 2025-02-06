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
        ], [
          'query' => [
            'destination' => $Request->getPathInfo()
          ]
        ]),
        '#options' => [
          'attributes' => [
            // 'target' => '_blank',
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
            // 'target' => '_blank',
            'class' => [
              'button',
              'button--primary'
            ]
          ]
        ]
      ];
      $query = $this->entityTypeManager()->getStorage('booking_config')->getQuery();
      $query->accessCheck(TRUE);
      $query->condition('type', $BookingConfigType->id());
      // l'entite adapte est $BookingConfig.
      $query->sort($BookingConfigType->getEntityType()->getKey('id'), 'DESC');
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
      $build['booking_equipes'][] = [
        '#type' => 'link',
        '#title' => $this->t('Add Booking equipes'),
        '#url' => Url::fromRoute("entity.booking_equipes.add_form", [
          'booking_config_type' => $BookingConfigType->id()
        ], [
          'query' => [
            'destination' => $Request->getPathInfo()
          ]
        ]),
        '#options' => [
          'attributes' => [
            // 'target' => '_blank',
            'class' => [
              'button',
              'button--primary'
            ]
          ]
        ]
      ];
      $query = $this->entityTypeManager()->getStorage('booking_equipes')->getQuery();
      $query->accessCheck(TRUE);
      $query->condition('booking_config_type', $BookingConfigType->id());
      $query->sort($BookingConfigType->getEntityType()->getKey('id'), 'DESC');
      $query->pager(100);
      $ids = $query->execute();
      /**
       *
       * @var \Drupal\booking_system\BookingEquipesListBuilder $ListBuilder
       */
      $ListBuilder = $this->entityTypeManager()->getListBuilder('booking_equipes');
      // dump($ListBuilder);
      $ListBuilder->setCustomIds($ids);
      $build['booking_equipes'][] = $ListBuilder->render();
    }
    else
      $this->messenger()->addWarning('This config not exist');
    //
    return $build;
  }

}