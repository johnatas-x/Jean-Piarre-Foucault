<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_utils\Enum\Days;

/**
 * Methods for CSV manipulations.
 */
class CsvHelper implements CsvHelperInterface {

  /**
   * {@inheritDoc}
   */
  public function csvToArray(string $file_path): array {
    $data = [];

    $handle = fopen($file_path, 'rb');

    if ($handle === FALSE) {
      return $data;
    }

    while (!feof($handle)) {
      $line = fgets($handle);

      if (!is_string($line)) {
        continue;
      }

      $data[] = str_getcsv($line, ';');
    }

    fclose($handle);

    $count = count($data);

    if ($count < self::MIN_ROWS) {
      throw new \RuntimeException('Not enough data in the CSV file');
    }

    $combined = [];

    /** @var array<int|string> $headers */
    $headers = $data[0];

    for ($increment = 1; $increment < $count; $increment++) {
      $combined[] = array_combine($headers, $data[$increment]);
    }

    return $combined;
  }

  /**
   * {@inheritDoc}
   */
  public function arrayFilter(array $csv_data, Versions $version, array|bool|null $last_record): array {
    $data_to_insert = [];

    foreach ($csv_data as $row) {
      $csv_date = $row['date_de_tirage'];

      if (!is_string($csv_date)) {
        continue;
      }

      $draw_date = DateTimePlus::createFromFormat($version->dateFormat(), $csv_date);
      $timestamp = $draw_date->getTimestamp();

      $data_to_insert[$timestamp] = [
        'version' => $version->value,
        'year' => (int) $draw_date->format('Y'),
        'month' => (int) $draw_date->format('m'),
        'day' => (int) $draw_date->format('d'),
        'which_draw' => !empty($row['1er_ou_2eme_tirage'])
          ? (int) $row['1er_ou_2eme_tirage']
          : 1,
        'day_of_week' => Days::fromMethod(
          $version->dayMethod(),
          $row['jour_de_tirage']
        )?->capitalizeFrenchLabel(),
      ];

      foreach (Balls::cases() as $ball) {
        if (!empty($data_to_insert[$timestamp][$ball->columnName()])) {
          continue;
        }

        $data_to_insert[$timestamp][$ball->columnName()] = !(empty($row[$ball->csvName()]))
          ? (int) $row[$ball->csvName()]
          : NULL;
      }

      if (is_array($last_record) && array_diff($data_to_insert[$timestamp], $last_record) === []) {
        unset($data_to_insert[$timestamp]);

        break;
      }
    }

    return $data_to_insert;
  }

}
