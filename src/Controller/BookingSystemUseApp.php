<?php

namespace Drupal\booking_system\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Stephane888\DrupalUtility\HttpResponse;
use Stephane888\Debug\ExceptionExtractMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\booking_system\Service\BookingManager\ManagerDate;

/**
 * Returns responses for booking_system routes.
 */
class BookingSystemUseApp extends ControllerBase {

  /**
   *
   * @var ManagerDate
   */
  protected $BookingMangerDate;

  public function __construct(ManagerDate $ManagerDate) {
    $this->BookingMangerDate = $ManagerDate;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('booking_system.app_manager'));
  }

  /**
   * Recupere les crenaux en function de la date du jour et de l'id de config.
   */
  public function GetCreneaux(Request $Request, $booking_config_type_id, $date_string) {
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
   */
  public function loadDefaultViews(Request $Request) {
    $booking_config_type_id = "test";
    return $this->Views($Request, $booking_config_type_id);
  }

}