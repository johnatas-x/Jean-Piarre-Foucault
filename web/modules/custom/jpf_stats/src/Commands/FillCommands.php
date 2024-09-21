<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Commands;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\jpf_stats\Batch\FillStatsBatch;
use Drupal\jpf_store\Enum\Versions;
use Drush\Commands\DrushCommands;

/**
 * Drush commands to fill stats in DB.
 */
class FillCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Fill stats in DB.
   *
   * @command fill-lotto-stats
   *
   * @aliases fls
   *
   * @usage drush fill-lotto-stats
   *   Fill stats to DB.
   */
  public function fill(): void {
    $version = Versions::currentVersion();

    if (!$version instanceof Versions) {
      throw new \RuntimeException(
        dt('Invalid current version.')
      );
    }

    // Put all needed information into batch array.
    $batch = [
      'operations' => FillStatsBatch::operations($version),
      'title' => dt('Fill stats in database.'),
      'init_message' => dt('Initialization.'),
      'error_message' => dt('An error occurred.'),
      'finished' => [
        FillStatsBatch::class,
        'finished',
      ],
    ];

    // Get the batch process all ready.
    batch_set($batch);
    $batch =& batch_get();

    // Because we are doing this on the back-end, we set progressive to false.
    $batch['progressive'] = FALSE;

    // Start processing the batch operations.
    drush_backend_batch_process();
  }

}