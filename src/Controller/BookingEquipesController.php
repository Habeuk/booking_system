<?php

namespace Drupal\booking_system\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\booking_system\Entity\BookingEquipesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BookingEquipesController.
 *
 *  Returns responses for Booking equipes routes.
 */
class BookingEquipesController extends ControllerBase implements ContainerInjectionInterface {

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
   * Displays a Booking equipes revision.
   *
   * @param int $booking_equipes_revision
   *   The Booking equipes revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($booking_equipes_revision) {
    $booking_equipes = $this->entityTypeManager()->getStorage('booking_equipes')
      ->loadRevision($booking_equipes_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('booking_equipes');

    return $view_builder->view($booking_equipes);
  }

  /**
   * Page title callback for a Booking equipes revision.
   *
   * @param int $booking_equipes_revision
   *   The Booking equipes revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($booking_equipes_revision) {
    $booking_equipes = $this->entityTypeManager()->getStorage('booking_equipes')
      ->loadRevision($booking_equipes_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $booking_equipes->label(),
      '%date' => $this->dateFormatter->format($booking_equipes->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Booking equipes.
   *
   * @param \Drupal\booking_system\Entity\BookingEquipesInterface $booking_equipes
   *   A Booking equipes object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BookingEquipesInterface $booking_equipes) {
    $account = $this->currentUser();
    $booking_equipes_storage = $this->entityTypeManager()->getStorage('booking_equipes');

    $langcode = $booking_equipes->language()->getId();
    $langname = $booking_equipes->language()->getName();
    $languages = $booking_equipes->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $booking_equipes->label()]) : $this->t('Revisions for %title', ['%title' => $booking_equipes->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all booking equipes revisions") || $account->hasPermission('administer booking equipes entities')));
    $delete_permission = (($account->hasPermission("delete all booking equipes revisions") || $account->hasPermission('administer booking equipes entities')));

    $rows = [];

    $vids = $booking_equipes_storage->revisionIds($booking_equipes);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\booking_system\Entity\BookingEquipesInterface $revision */
      $revision = $booking_equipes_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $booking_equipes->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.booking_equipes.revision', [
            'booking_equipes' => $booking_equipes->id(),
            'booking_equipes_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $booking_equipes->toLink($date)->toString();
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
              Url::fromRoute('entity.booking_equipes.translation_revert', [
                'booking_equipes' => $booking_equipes->id(),
                'booking_equipes_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.booking_equipes.revision_revert', [
                'booking_equipes' => $booking_equipes->id(),
                'booking_equipes_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.booking_equipes.revision_delete', [
                'booking_equipes' => $booking_equipes->id(),
                'booking_equipes_revision' => $vid,
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

    $build['booking_equipes_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
