<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\jpf_store\Enum\Versions;

/**
 * Provides an interface for all database methods.
 */
interface DatabaseInterface {

  /**
   * Import data from CSV file to database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The file version.
   *
   * @throws \Exception
   */
  public function importCsvFile(Versions $version): void;

  /**
   * Get the last record.
   *
   * @return array<string, string|null>|bool|null
   *   Associative array of the last record.
   */
  public function getLastRecord(): array|bool|null;

  /**
   * Delete table in DB.
   *
   * @param string $table
   *   The table name.
   */
  public function deleteTable(string $table): void;

  /**
   * Update draws count in version table.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The file version.
   * @param int $new_records
   *   Number of new records.
   */
  public function updateDrawsCount(Versions $version, int $new_records): void;

}
