<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Commands;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\drush_batch_bar\Commands\DrushBatchCommands;
use Drupal\jpf_store\Batch\FillDataBatch;
use Drupal\jpf_store\Enum\Versions;
use Drush\Commands\DrushCommands;

/**
 * Drush commands to fill data in DB.
 */
class FillCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Fill data from CSV to DB.
   *
   * @param array<array<mixed>|string|bool|null> $options
   *   Options array.
   *
   * @command fill-lotto-draws-data
   *
   * @option versions List of versions to fill, separated with a comma.
   *   Use the 'all' option instead if you want all versions.
   * @option all Fill all versions.
   *   Don't use this option with the 'versions' option.
   *
   * @aliases fldd
   *
   * @usage drush fill-lotto-draws-data --versions=v1,v2
   *   Fill v1 and v2 files to DB.
   * @usage drush fldd --all
   *   Fill all files to DB
   */
  public function fill(array $options = ['versions' => NULL, 'all' => FALSE]): void {
    $batch = new DrushBatchCommands(
      operations: FillDataBatch::operations($this->optionsChecker($options)),
      drush_io: $this->io(),
      title: 'Import data to database.',
      finished: [
        FillDataBatch::class,
        'finished',
      ]
    );

    $batch->execute();
  }

  /**
   * Check options and returns versions to fill.
   *
   * @param array<array<mixed>|string|bool|null> $options
   *   Command options.
   *
   * @return array<string>
   *   The options.
   */
  private function optionsChecker(array $options): array {
    if (is_string($options['versions']) && $options['all'] === TRUE) {
      throw new \InvalidArgumentException(
        t('Do not use "versions" and "all" options together.')->render()
      );
    }

    if ($options['versions'] === NULL && $options['all'] === FALSE) {
      throw new \InvalidArgumentException(
        t('You must use at least one option between "versions" and "all".')->render()
      );
    }

    if (is_string($options['versions'])) {
      $versions = explode(',', $options['versions']);
      $not_allowed_versions = array_diff($versions, Versions::values());

      if (!empty($not_allowed_versions)) {
        $message = $this->formatPlural(
          count($not_allowed_versions),
          'This version is undefined or not allowed : @item',
          'These versions are undefined or not allowed : @items',
          [
            '@item' => reset($not_allowed_versions),
            '@items' => implode(', ', $not_allowed_versions),
          ]
        )->render();

        throw new \InvalidArgumentException($message);
      }
    }

    return $versions ?? Versions::values();
  }

}
