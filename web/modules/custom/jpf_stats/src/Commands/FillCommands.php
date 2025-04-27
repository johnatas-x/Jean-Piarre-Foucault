<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Commands;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\drush_batch_bar\Commands\DrushBatchCommands;
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
        t('Invalid current version.')->render()
      );
    }

    $batch = new DrushBatchCommands(
      operations: FillStatsBatch::operations($version),
      drush_io: $this->io(),
      title: 'Fill stats in database.',
      finished: [
        FillStatsBatch::class,
        'finished',
      ]
    );

    $batch->execute();
  }

}
