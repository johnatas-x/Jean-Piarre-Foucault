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
  final protected const string FILE_EXTENSION = '.csv';

  /**
   * Define module path to avoid using \Drupal::service('extension.list.module')->getPath('jpf_store').
   */
  final protected const string MODULE_PATH = 'modules/custom/jpf_store';

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
   * Version file directory.
   *
   * @return string
   *   The path of directory.
   */
  public function filePath(): string {
    return DRUPAL_ROOT . '/' . self::MODULE_PATH . "/assets/doc/$this->value";
  }

  /**
   * Files name of versions.
   *
   * @return array<int, string>|false
   *   The files name of the version.
   */
  public function filesName(): array|false {
    return glob($this->filePath() . '/*' . self::FILE_EXTENSION);
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
