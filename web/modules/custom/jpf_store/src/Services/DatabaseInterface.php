<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\jpf_store\Enum\Versions;

/**
 * Provides an interface for all database methods.
 */
interface DatabaseInterface {

  /**
   * Init a select query in lotto draws table.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The select query.
   */
  public function selectLotto(): SelectInterface;

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
   * Get the ID of the last record.
   *
   * @return int|null
   *   The ID, NULL if no record.
   */
  public function getLastRecordId(): ?int;

  /**
   * Delete table in DB.
   *
   * @param string $table
   *   The table name.
   */
  public function deleteTable(string $table): void;

  /**
   * Get count of draws for the given version.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   *
   * @return int
   *   Count of draws.
   */
  public function getCountRecords(Versions $version): int;

  /**
   * Update draws count in version table.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The file version.
   * @param int $new_records
   *   Number of new records.
   */
  public function updateDrawsCount(Versions $version, int $new_records): void;

  /**
   * Archive prediction with given ID.
   *
   * @param string|null $record_id
   *   The record ID to be linked to the prediction.
   */
  public function archivePrediction(?string $record_id): void;

}
