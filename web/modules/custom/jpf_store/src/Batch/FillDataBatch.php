<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Batch;

use Drupal\drush_batch_bar\Batch\DrushBatchBar;
use Drupal\jpf_store\Enum\Versions;

/**
 * Batch methods for FillCommands.
 */
class FillDataBatch extends DrushBatchBar {

  /**
   * The finished success message.
   */
  protected const string SUCCESS_MESSAGE = 'versions imported';

  /**
   * Batch operations for fill data drush command.
   *
   * @param array<string> $versions
   *   Versions to import.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   }>
   *   The batch operations.
   */
  public static function operations(array $versions): array {
    $operations = [];
    $increment = 1;

    foreach ($versions as $version) {
      $operations[] = [
        [self::class, 'process'],
        [
          Versions::from($version),
          \Drupal::translation()
            ->translate('Import data : version @chunk / @count',
              ['@chunk' => $increment, '@count' => count($versions)]
          ),
        ],
      ];

      $increment++;
    }

    return $operations;
  }

  /**
   * Import data to database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   * @param string $details
   *   Details to follow command progress.
   * @param array<mixed> $context
   *   The batch context.
   */
  public static function process(Versions $version, string $details, array &$context): void {
    parent::initProcess($details, $context);

    try {
      \Drupal::service('jpf_store.database')->importCsvFile($version);
      $context['results']['success']++;
      $context['message'] = '[OK] ' . $version->filename();
    }
    catch (\Throwable $exception) {
      $context['results']['error']++;
      $context['message'] = '[KO] ' . $exception->getMessage();
    }
  }

}
