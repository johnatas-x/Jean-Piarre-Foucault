<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jpf_algo\Entity\Prediction;
use Drupal\jpf_store\Enum\Versions;

/**
 * Database methods for this module.
 */
class Database implements DatabaseInterface {

  /**
   * The Database constructor.
   *
   * @param \Drupal\jpf_store\Services\CsvHelperInterface $csvHelper
   *   The CSV Helper methods.
   * @param \Drupal\Core\Database\Connection $databaseConnection
   *   The database connection.
   * @param \Drupal\jpf_store\Services\SchemaInterface $schema
   *   The custom schema service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(
    protected CsvHelperInterface $csvHelper,
    protected Connection $databaseConnection,
    protected SchemaInterface $schema,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function selectLotto(): SelectInterface {
    return $this->databaseConnection->select(SchemaInterface::LOTTO_DRAWS_TABLE, SchemaInterface::LOTTO_TABLE_ALIAS);
  }

  /**
   * {@inheritDoc}
   */
  public function importCsvFile(Versions $version): void {
    $data = $this->csvHelper->csvToArray($version->filePath());
    $last_record = $this->getLastRecord();
    $needed_data = $this->csvHelper->arrayFilter($data, $version, $last_record);
    sort($needed_data);

    if (count($needed_data) === 0) {
      return;
    }

    $database_columns = array_keys($this->schema->lottoDrawsFields());
    array_shift($database_columns);

    $query = $this->databaseConnection
      ->insert(SchemaInterface::LOTTO_DRAWS_TABLE)
      ->fields($database_columns);

    foreach ($needed_data as $record) {
      $query->values($record);
    }

    $query->execute();

    $this->updateDrawsCount($version, count($needed_data));
    $this->archivePrediction($last_record['id'] ?? NULL);
  }

  /**
   * {@inheritDoc}
   */
  public function getLastRecord(): array|bool|null {
    $record = $this->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS)
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchAssoc();

    if (is_bool($record) || is_null($record)) {
      return $record;
    }

    $validatedRecord = [];

    foreach ($record as $key => $value) {
      if (!is_string($key) || ((!is_string($value)) && !is_null($value))) {
        throw new \UnexpectedValueException('Bad type.');
      }

      $validatedRecord[$key] = $value;
    }

    return $validatedRecord;
  }

  /**
   * {@inheritDoc}
   */
  public function getLastRecordId(): ?int {
    $record = $this->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, ['id'])
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchField();

    return is_numeric($record)
      ? (int) $record
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function deleteTable(string $table): void {
    $schema = $this->databaseConnection->schema();

    if (!$schema->tableExists($table)) {
      return;
    }

    $schema->dropTable($table);
  }

  /**
   * {@inheritDoc}
   */
  public function getCountRecords(Versions $version): int {
    $records = $this->databaseConnection
      ->select(SchemaInterface::LOTTO_VERSIONS, 'lv')
      ->fields('lv', ['draws_count'])
      ->condition('version', $version->value)
      ->execute()
      ?->fetchField();

    return is_numeric($records)
      ? (int) $records
      : 0;
  }

  /**
   * {@inheritDoc}
   */
  public function updateDrawsCount(Versions $version, int $new_records): void {
    $this->databaseConnection
      ->update(SchemaInterface::LOTTO_VERSIONS)
      ->fields(['draws_count' => $this->getCountRecords($version) + $new_records])
      ->condition('version', $version->value)
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function archivePrediction(?string $record_id): void {
    if ($record_id === NULL || !$this->moduleHandler->moduleExists('jpf_algo')) {
      return;
    }

    $this->databaseConnection->update(Prediction::LOTTO_PREDICT_TABLE)
      ->isNull('draw_id')
      ->fields(['draw_id' => $record_id])
      ->execute();
  }

}
