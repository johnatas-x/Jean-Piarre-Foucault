<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

/**
 * Methods for CSV manipulations.
 */
class CsvHelper implements CsvHelperInterface {

  /**
   * {@inheritDoc}
   */
  public function csvToArray(string $filename): array {
    $data = [];

    $handle = fopen($filename, 'rb');

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

}
