<?php

namespace Drupal\booking_system\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\booking_system\Entity\BookingConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BookingConfigController.
 *
 *  Returns responses for Booking config routes.
 */
class BookingConfigController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Booking config revision.
   *
   * @param int $booking_config_revision
   *   The Booking config revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($booking_config_revision) {
    $booking_config = $this->entityTypeManager()->getStorage('booking_config')
      ->loadRevision($booking_config_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('booking_config');

    return $view_builder->view($booking_config);
  }

  /**
   * Page title callback for a Booking config revision.
   *
   * @param int $booking_config_revision
   *   The Booking config revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($booking_config_revision) {
    $booking_config = $this->entityTypeManager()->getStorage('booking_config')
      ->loadRevision($booking_config_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $booking_config->label(),
      '%date' => $this->dateFormatter->format($booking_config->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Booking config.
   *
   * @param \Drupal\booking_system\Entity\BookingConfigInterface $booking_config
   *   A Booking config object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BookingConfigInterface $booking_config) {
    $account = $this->currentUser();
    $booking_config_storage = $this->entityTypeManager()->getStorage('booking_config');

    $langcode = $booking_config->language()->getId();
    $langname = $booking_config->language()->getName();
    $languages = $booking_config->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $booking_config->label()]) : $this->t('Revisions for %title', ['%title' => $booking_config->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all booking config revisions") || $account->hasPermission('administer booking config entities')));
    $delete_permission = (($account->hasPermission("delete all booking config revisions") || $account->hasPermission('administer booking config entities')));

    $rows = [];

    $vids = $booking_config_storage->revisionIds($booking_config);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\booking_system\Entity\BookingConfigInterface $revision */
      $revision = $booking_config_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $booking_config->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.booking_config.revision', [
            'booking_config' => $booking_config->id(),
            'booking_config_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $booking_config->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.booking_config.translation_revert', [
                'booking_config' => $booking_config->id(),
                'booking_config_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.booking_config.revision_revert', [
                'booking_config' => $booking_config->id(),
                'booking_config_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.booking_config.revision_delete', [
                'booking_config' => $booking_config->id(),
                'booking_config_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['booking_config_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
