<?php

namespace Drupal\booking_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Booking config entities.
 *
 * @ingroup booking_system
 */
class BookingConfigListBuilder extends EntityListBuilder {
  protected $ids = NULL;

  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('#ID');
    $header['name'] = $this->t('Title');
    return $header + parent::buildHeader();
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\booking_system\Entity\BookingConfig $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute($entity->label(), 'entity.booking_config.edit_form', [
      'booking_config' => $entity->id()
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
