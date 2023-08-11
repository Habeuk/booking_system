<?php

namespace Drupal\booking_system\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Booking equipes entities.
 */
class BookingEquipesViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
