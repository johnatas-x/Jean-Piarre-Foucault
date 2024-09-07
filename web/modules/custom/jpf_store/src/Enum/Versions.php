<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Enum;

use Drupal\jpf_store\Traits\EnumToArray;

/**
 * Existing versions of lotto files.
 */
enum Versions: string {

  use EnumToArray;

  case First = 'v1';
  case Second = 'v2';
  case Third = 'v3';
  case Fourth = 'v4';
  case Fifth = 'v5';

  /**
   * Files extension.
   */
  private const string FILE_EXTENSION = '.csv';

  /**
   * Archives extension.
   */
  private const string ARCHIVE_EXTENSION = '.zip';

  /**
   * Define module path to avoid using \Drupal::service('extension.list.module')->getPath('jpf_store').
   */
  final protected const string MODULE_PATH = 'modules/custom/jpf_store';

  /**
   * Filename of versions.
   *
   * @return string
   *   The filename of the version.
   */
  public function filename(): string {
    return match ($this) {
      self::First => 'loto',
      self::Second => 'nouveau_loto',
      self::Third => 'loto2017',
      self::Fourth => 'loto_201902',
      self::Fifth => 'loto_201911',
    };
  }

  /**
   * Begin date.
   *
   * @return string
   *   The first month of the version.
   */
  public function begin(): string {
    return match ($this) {
      self::First => '197605',
      self::Second => '200810',
      self::Third => '201703',
      self::Fourth => '201902',
      self::Fifth => '201911',
    };
  }

  /**
   * End date.
   *
   * @return string
   *   The last month of the version.
   */
  public function end(): string {
    return match ($this) {
      self::First => '200810',
      self::Second => '201703',
      self::Third => '201902',
      self::Fourth => '201911',
      self::Fifth => '',
    };
  }

  /**
   * Version file path.
   *
   * @return string
   *   The path of directory.
   */
  public function filePath(): string {
    return DRUPAL_ROOT . '/' . self::MODULE_PATH .
      "/assets/doc/$this->value/{$this->filename()}/" . self::FILE_EXTENSION;
  }

  /**
   * The date format in CSV file depends on version.
   *
   * @return string
   *   The date format.
   */
  public function dateFormat(): string {
    return match ($this) {
      self::First => 'Ymd',
      default => 'd/m/Y',
    };
  }

  /**
   * The day method to use depends on version.
   *
   * @return string
   *   The day method.
   */
  public function dayMethod(): string {
    return match ($this) {
      self::First => 'uppercaseDayCode',
      default => 'uppercaseFrenchLabel',
    };
  }

}
