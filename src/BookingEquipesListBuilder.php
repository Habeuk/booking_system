<?php

namespace Drupal\booking_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Booking equipes entities.
 *
 * @ingroup booking_system
 */
class BookingEquipesListBuilder extends EntityListBuilder {
  protected $ids = NULL;

  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Booking equipes ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\booking_system\Entity\BookingEquipes $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute($entity->label(), 'entity.booking_equipes.edit_form', [
      'booking_equipes' => $entity->id()
    ]);
    return $row + parent::buildRow($entity);
  }

  public function load() {
    if ($this->ids === NULL)
      $entity_ids = $this->getEntityIds();
    else
      $entity_ids = $this->ids;
    return $this->storage->loadMultiple($entity_ids);
  }

  public function setCustomIds($ids) {
    $this->ids = $ids;
  }

}
