<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Core\Database\Connection;
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
    return $this->databaseConnection
      ->select(SchemaInterface::LOTTO_DRAWS_TABLE, 'lotto')
      ->fields('lotto')
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchAssoc();
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
  public function updateDrawsCount(Versions $version, int $new_records): void {
    $current_records = $this->databaseConnection
      ->select(SchemaInterface::LOTTO_VERSIONS, 'lv')
      ->fields('lv', ['draws_count'])
      ->condition('version', $version->value)
      ->execute()
      ?->fetchField();

    $this->databaseConnection
      ->update(SchemaInterface::LOTTO_VERSIONS)
      ->fields(['draws_count' => $current_records + $new_records])
      ->condition('version', $version->value)
      ->execute();
  }

}
