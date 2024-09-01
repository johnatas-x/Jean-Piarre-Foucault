<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

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
   * The Database constructor.
   *
   * @param \Drupal\jpf_store\Services\CsvHelperInterface $csv_helper
   *   The CSV Helper methods.
   */
  public function __construct(CsvHelperInterface $csv_helper) {
    $this->csvHelper = $csv_helper;
  }

  /**
   * {@inheritDoc}
   */
  public function importCsvFile(string $filepath): void {
    $data = $this->csvHelper->csvToArray($filepath);
    $needed_data = $this->csvHelper->arrayFilter($data);

    // TODO insert data to DB.
  }

}
