<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Core\Database\Connection;
use Drupal\jpf_store\Enum\Balls;
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
   * The Database constructor.
   *
   * @param \Drupal\jpf_store\Services\CsvHelperInterface $csv_helper
   *   The CSV Helper methods.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The database connection.
   */
  public function __construct(CsvHelperInterface $csv_helper, Connection $database_connection) {
    $this->csvHelper = $csv_helper;
    $this->databaseConnection = $database_connection;
  }

  /**
   * {@inheritDoc}
   *
   * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
   */
  public function lottoDrawsFields(): array {
    $fields = [
      'id' => [
        'description' => 'The primary identifier.',
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ],
      'year' => [
        'description' => 'Year',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 4,
      ],
      'month' => [
        'description' => 'Month',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 2,
      ],
      'day' => [
        'description' => 'Day',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 2,
      ],
      'which_draw' => [
        'description' => 'First or second draw',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 1,
      ],
      'day_of_week' => [
        'description' => 'Day of week',
        'type' => 'varchar',
        'length' => 32,
        'not null' => TRUE,
      ],
    ];

    foreach (Balls::cases() as $ball) {
      $fields["ball_{$ball->numeric()}"] = [
        'description' => $ball->value,
        'type' => 'int',
        'unsigned' => TRUE,
        'length' => 2,
      ];
    }

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function importCsvFile(Versions $version): void {
    $data = $this->csvHelper->csvToArray($version->filePath());
    $last_record = $this->getLastRecord();
    $needed_data = $this->csvHelper->arrayFilter($data, $version, $last_record);
    sort($needed_data);

    $database_columns = array_keys($this->lottoDrawsFields());
    array_shift($database_columns);

    $query = $this->databaseConnection
      ->insert(self::LOTTO_DRAWS_TABLE)
      ->fields($database_columns);

    foreach ($needed_data as $record) {
      $query->values($record);
    }

    $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function getLastRecord(): array|bool|null {
    return $this->databaseConnection
      ->select(self::LOTTO_DRAWS_TABLE, 'lotto')
      ->fields('lotto')
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchAssoc();
  }

}
