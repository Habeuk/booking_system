<?php

namespace Drupal\booking_system_schedule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\DrupalUtility\HttpResponse;
use Stephane888\Debug\ExceptionExtractMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\booking_system\Services\BookingManager\ManagerDate;
use Drupal\booking_system\Services\BookingManager\ManagerCreneaux;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;

/**
 * Returns responses for booking_system routes.
 */
class BookingSystemScheduleUseApp extends ControllerBase {
  
  /**
   * Builds the response to showing the Vue-js app.
   * On definit les urls pour l'initialisation de l'application.
   */
  public function build() {
    $booking_config_type_id = "test"; // on definit cet id juste pour les tests.
    $urlCalendar = Url::fromRoute("booking_system.app_load_config_calendar");
    $urlCreneaux = Url::fromRoute("booking_system.app_load_creneaux", [
      'booking_config_type_id' => $booking_config_type_id,
      'date' => null
    ]);
    $urlSave = Url::fromRoute("booking_system.save_reservation", [
      'booking_config_type_id' => $booking_config_type_id
    ]);
    $build['content'] = [
      '#type' => 'html_tag',
      '#tag' => 'section',
      "#attributes" => [
        'id' => 'app',
        'data-url-calendar' => '/' . $urlCalendar->getInternalPath(),
        'data-url-creneaux' => '/' . $urlCreneaux->getInternalPath(),
        'data-url-save' => '/' . $urlSave->getInternalPath(),
        'class' => [
          'm-5',
          'p-5'
        ]
      ]
    ];
    $build['content']['#attached']['library'][] = 'booking_system/booking_system_app2';
    return $build;
  }
  
  /**
   * Permet de charger les informations du planning.
   *
   * @param string $booking_config_type_id
   * @param string $date
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function loadConfigSchedule(Request $Request, $booking_config_type_id) {
    try {
      $datas = Json::decode($Request->getContent());
      if (!empty($datas['filtres'])) {
        // applies
      }
      $configs = [];
      return HttpResponse::response($configs);
    }
    catch (\Exception $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
    catch (\Error $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
  }
  
}