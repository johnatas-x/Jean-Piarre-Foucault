<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\jpf_store\Enum\Versions;

/**
 * Database methods.
 */
class Database implements DatabaseInterface {

  /**
   * The CSV Helper methods.
   *
   * @var \Drupal\jpf_store\Services\CsvHelperInterface
   */
  protected CsvHelperInterface $csvHelper;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $databaseConnection;

  /**
   * The custom schema service.
   *
   * @var \Drupal\jpf_store\Services\SchemaInterface
   */
  protected SchemaInterface $schema;

  /**
   * The Database constructor.
   *
   * @param \Drupal\jpf_store\Services\CsvHelperInterface $csv_helper
   *   The CSV Helper methods.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The database connection.
   * @param \Drupal\jpf_store\Services\SchemaInterface $schema
   *   The custom schema service.
   */
  public function __construct(
    CsvHelperInterface $csv_helper,
    Connection $database_connection,
    SchemaInterface $schema,
  ) {
    $this->csvHelper = $csv_helper;
    $this->databaseConnection = $database_connection;
    $this->schema = $schema;
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

}
