<?php

namespace Drupal\seasonal_paragraphs;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Config\ConfigFactoryInterface;

class Season implements CacheableDependencyInterface {
  use StringTranslationTrait;

  /**
   * The name of the base field.
   */
  public const FIELDNAME = 'seasonal';

  /**
   * The summer season.
   */
  public const SUMMER = 'summer';

  /**
   * The winter season.
   */
  public const WINTER = 'winter';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a Season object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  public function allowedValuesCallback(FieldStorageDefinitionInterface $definition, FieldableEntityInterface $entity = NULL, bool &$cacheable = TRUE): array {
    $options = [
      self::SUMMER => $this->t('only visible in summer'),
      self::WINTER => $this->t('only visible in winter'),
    ];
    $this->moduleHandler->invokeAll('seasonal_paragraphs_alter_available_seasons', [&$options, $definition, $entity, &$cacheable]);
    return $options;
  }

  /**
   * Returns the current season.
   *
   * @return string
   */
  public function getCurrentSeason(): string {
    $config = $this->configFactory->get('system.site');
    $now = new \DateTime();
    $from = \DateTime::createFromFormat('!Y-m-d', $config->get('summer_season.from'));
    $from->setDate($now->format('Y'), $from->format('m'), $from->format('d'));
    $to = \DateTime::createFromFormat('!Y-m-d', $config->get('summer_season.to'));
    $to->setDate($now->format('Y'), $to->format('m'), $to->format('d'));
    $season = self::WINTER;
    if ($now >= $from and $now < $to) {
      $season = self::SUMMER;
    }
    $this->moduleHandler->invokeAll('seasonal_paragraphs_alter_current_season', [&$season]);
    return $season;
  }

  /**
   * @inheritDoc
   */
  public function getCacheContexts(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getCacheTags(): array {
    $config = $this->configFactory->get('system.site');
    return Cache::mergeTags($config->getCacheTags(), self::getExpiringCacheTags());
  }

  /**
   * @inheritDoc
   */
  public function getCacheMaxAge(): int {
    return Cache::PERMANENT;
  }

  /**
   * Returns the time-based cache tags that might expire over time, even without data change.
   *
   * @return string[]
   *
   * @see \seasonal_paragraphs_cron()
   */
  public static function getExpiringCacheTags(): array {
    return ['seasonal_paragraphs'];
  }
}
