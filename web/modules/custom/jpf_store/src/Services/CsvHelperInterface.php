<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

/**
 * Interface for CSV methods.
 */
interface CsvHelperInterface {

  /**
   * Minimum of rows in the file (header row and at least one data row).
   */
  final public const int MIN_ROWS = 2;

  /**
   * Extract needed data from CSV and store in array.
   *
   * @param string $filename
   *   The CSV filename.
   *
   * @return array<int, array<int|string, string|null>>
   *   Array of data.
   *
   * @throws \RuntimeException
   */
  public function csvToArray(string $filename): array;

}
