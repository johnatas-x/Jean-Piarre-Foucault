<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

/**
 * Provides an interface for all database methods.
 */
interface DatabaseInterface {

  /**
   * The name of the table which contains all lotto draws.
   */
  final public const string LOTTO_DRAWS_TABLE = 'lotto_draws';

  /**
   * Import data from CSV file to database.
   *
   * @param string $filepath
   *   The path of CSV file.
   * @param string $version
   *   The CSV file version.
   */
  public function importCsvFile(string $filepath, string $version): void;

}
