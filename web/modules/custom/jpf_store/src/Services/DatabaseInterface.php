<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\jpf_store\Enum\Versions;

/**
 * Provides an interface for all database methods.
 */
interface DatabaseInterface {

  /**
   * The name of the table which contains all lotto draws.
   */
  final public const string LOTTO_DRAWS_TABLE = 'lotto_draws';

  /**
   * Lotto draws schema fields.
   *
   * @return array<string, array<string, bool|int|string>>
   *   The fields.
   */
  public function lottoDrawsFields(): array;

  /**
   * Import data from CSV file to database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The file version.
   *
   * @throws \Exception
   */
  public function importCsvFile(Versions $version): void;

}
