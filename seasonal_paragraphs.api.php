<?php

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allows altering the current season for seasonal paragraph visibility.
 *
 * @param string $currentSeason
 *
 * @see \Drupal\seasonal_paragraphs\Season::getCurrentSeason()
 */
function hook_seasonal_paragraphs_alter_current_season(string &$currentSeason): void {
  if ((new \DateTime())->format('m-d') == '12-24') {
    $currentSeason = 'CHRISTMAS';
  }
}

/**
 * Allows altering the available seasons for seasonal visibility on field storage level.
 *
 * @param array $options
 * @param \Drupal\field\Entity\FieldStorageConfig $definition
 * @param \Drupal\Core\Entity\FieldableEntityInterface|null $entity
 * @param bool $cacheable
 *
 * @see seasonal_paragraphs_allowed_values()
 */
function hook_seasonal_paragraphs_alter_available_seasons(array &$options, FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, bool &$cacheable = TRUE): void {
  $options[] = ['CHRISTMAS' => t('Merry christmas')];
}

/**
 * @} End of "addtogroup hooks".
 */
