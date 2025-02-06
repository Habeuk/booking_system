<?php

/**
 * @file
 * Contains booking_config.page.inc.
 *
 * Page callback for Booking config entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Booking config templates.
 *
 * Default template: booking_config.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_booking_config(array &$variables) {
  // Fetch BookingConfig Entity Object.
  $booking_config = $variables['elements']['#booking_config'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
