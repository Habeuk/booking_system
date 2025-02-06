<?php

/**
 * @file
 * Contains booking_equipes.page.inc.
 *
 * Page callback for Booking equipes entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Booking equipes templates.
 *
 * Default template: booking_equipes.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_booking_equipes(array &$variables) {
  // Fetch BookingEquipes Entity Object.
  $booking_equipes = $variables['elements']['#booking_equipes'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
