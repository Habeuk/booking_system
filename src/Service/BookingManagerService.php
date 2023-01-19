<?php

namespace Drupal\booking_system\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Google\Service\Kgsearch\Resource\Entities;

/**
 * Manage the booking system
 */
class BookingManagerService extends ControllerBase
{

  protected $currentUser;
  protected $em;

  public function __construct(AccountInterface $currentUser, EntityTypeManager $em)
  {
    $this->currentUser = $currentUser;
    $this->em = $em;
  }

  /**
   * 
   * {@inheritdoc}
   * 
   */
  public function generateDates()
  {
    $data = [];
    $config = $this->config('booking_system.settings')->getRawData();
    $data["globalDiscount"] = $config["reduction"];
    $data["numberOfDisplayedDays"] = $config["number_of_days"];
    $data["maxPeoples"] = $config["number_of_persons"];
    $disabledDaysOfTheWeek = $this->getDisabledDaysOfTheWeek($config["jours"]);
    $data["disabledDays"] = $disabledDaysOfTheWeek;

    /**
     *
     * @var \Drupal\booking_system\Entity\BookingDateEntity $entity
     */
    $disabledDays = $this->em->getStorage('booking_system_date')->loadMultiple(); //getQuery permet de construire une requête
    /*$disabledDates = $this->getDisabledDates($disabledDays);
    $data['disabledDates'] = $disabledDates;*/
    //dump($disabledDays);
    return $data;
  }

  /**
   * 
   * {@inheritdoc}
   * 
   */
  public function generateDisabledDates(){
    $data=[];
    $entities = $this->em->getStorage('booking_system_date')->loadMultiple(); //getQuery permet de construire une requête
    $today = strtotime('now');
    /**
     *
     * @var \Drupal\booking_system\Entity\BookingSystemDate $entity
     */
    foreach($entities as $entity){
      $date_status = $entity->get("status")->getValue()[0]['value'];
      $date_value  = strtotime($entity->get("start_date")->getValue()[0]['value']);
      if(!$date_status && $date_value >= $today){
        $data[] =  $date_value;
      }
    }
    return $data;
  }

  /**
   * 
   * {@inheritdoc}
   * 
   */
  public function generateSchdules($day)
  {
    $data = [];

    $dayNames = [
      'Sunday' => 0,
      'Monday' => 1,
      'Tuesday' => 2,
      'Wednesday' => 3,
      'Thursday' => 4,
      'Friday' => 5,
      'Saturday' => 6,
    ];

    // retrive the global config alues
    $config = $this->config('booking_system.settings')->getRawData();

    //very if the date is valid
    $today = strtotime("now");
    if ($day <= $today) {
      $data["error"] = "The day sent must be posterior to the current day";
      return $data;
    }

    $numberOfDays = (int) $config["number_of_days"];
    $maxDate = strtotime("+$numberOfDays day");
    if ($day > $maxDate) {
      $data["error"] = "The day sent must be in a interval time of $numberOfDays from the current date";
      return $data;
    }
    //check if the date is modified specificaly


    //else get the default configuration and render it
    $dayOfTheWeek = date('l', $day);
    $isValidated = $config["jours"][$dayNames[$dayOfTheWeek]]["status"];
    if ($isValidated != 1) {
      $data["error"] = "This day is not activated";
      return $data;
    }
    $selectedDay = $config["jours"][$dayNames[$dayOfTheWeek]];

    $periods = [];

    foreach ($selectedDay["periodes"] as $period) {
      $dayPeriod = [];
      $dayPeriod["name"] = $period["label"];
      $dayPeriod["times"] = $this->getPeriodes($period["h_d__m_d"], $period["h_f__m_f"], (int) $period["intervalle"], $day, $period["decallage"]);

      $periods[] = $dayPeriod;
    }
    return $periods;
  }

  /**
   * 
   * {@inheritdoc}
   * get the disabled days
   * 
   */
  public function getDisabledDaysOfTheWeek(array $days)
  {
    $disabledDays = [];
    foreach ($days as $k => $day) {
      if ($day["status"] == 0) {
        $disabledDays[$k] = $k;
      }
    }
    $disabledDays = array_values($disabledDays);
    //dump($disabledDays);
    return $disabledDays;
  }

  /**
   * 
   * {@inheritdoc}
   * 
   */

  public function getDisabledDates(array $dates) {
    $disabledDates = [];
    foreach ($dates as $d => $date) {
      if ($date['status'] == 0) {
        $disabledDates[$d] = $date['date_debut'];
      }
    }
    $disabledDates = array_values($disabledDates);

    return $disabledDates;
  }

  /**
   * 
   * {@inheritdoc}
   * get the periods
   */
  public function getPeriodes($start, $end, $gap, $day, $intervalle)
  {
    $intervalle = (int) $intervalle;

    $times = [];
    $date = date("y-m-d", $day) . " " . $start;
    $time = strtotime($date);
    $startHour = (int) explode(":", $start)[0];
    $endHour = (int) explode(":", $end)[0];

    $newGap = $gap * 60;
    $maxTimestamp = ($endHour - $startHour) * 3600 + $time;
    $timestamp = $time;

    while ($timestamp <= $maxTimestamp) {
      $times[] = date("H:i", $timestamp);
      $timestamp += $newGap;
      $timestamp += $intervalle;
    }

    return $times;
  }
}
