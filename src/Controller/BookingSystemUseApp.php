<?php

namespace Drupal\booking_system\Controller;

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
class BookingSystemUseApp extends ControllerBase {
  
  /**
   *
   * @var ManagerDate
   */
  protected $BookingMangerDate;
  
  /**
   *
   * @var ManagerCreneaux
   */
  protected $ManagerCreneaux;
  
  public function __construct(ManagerDate $ManagerDate, ManagerCreneaux $ManagerCreneaux) {
    $this->BookingMangerDate = $ManagerDate;
    $this->ManagerCreneaux = $ManagerCreneaux;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('booking_system.app_manager_date'), $container->get('booking_system.app_manager_creneaux'));
  }
  
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
   * Permet de charger une configuration à partir de son id.
   *
   * @param Request $Request
   * @param string $booking_config_type_id
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function Views(Request $Request, $booking_config_type_id) {
    try {
      $configs = $this->BookingMangerDate->loadBookingConfig($booking_config_type_id);
      return HttpResponse::response($configs);
    }
    catch (\Exception $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
    catch (\Error $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
  }
  
  /**
   * Permet de charger la configuration par defaut.
   * ( Actuelment pour les tests ).
   */
  public function loadConfigCalandar(Request $Request) {
    $booking_config_type_id = "test";
    // return HttpResponse::response($configs);
    return $this->Views($Request, $booking_config_type_id);
  }
  
  /**
   * Enregistrer un creneau.
   *
   * @param string $booking_config_type_id
   */
  public function SaveReservation(Request $Request, string $booking_config_type_id) {
    try {
      $values = Json::decode($Request->getContent());
      $configs = $this->BookingMangerDate->saveCreneaux($booking_config_type_id, $values);
      return HttpResponse::response($configs);
    }
    catch (\Exception $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
    catch (\Error $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 435);
    }
  }
  
  /**
   * Permet de recuperer les données de configurations pour la construction des
   * creneaux.
   *
   * @param string $booking_config_type_id
   * @param string $date
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function loadConfisCreneaux($booking_config_type_id, $date) {
    try {
      $configs = $this->ManagerCreneaux->loadCreneaux($booking_config_type_id, $date);
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