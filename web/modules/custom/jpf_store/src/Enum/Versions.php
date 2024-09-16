<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Enum;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\jpf_utils\Traits\EnumToArray;

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
  public const string FILE_EXTENSION = '.csv';

  /**
   * Archives extension.
   */
  private const string ARCHIVE_EXTENSION = '.zip';

  /**
   * Define module path to avoid using \Drupal::service('extension.list.module')->getPath('jpf_store').
   */
  private const string MODULE_PATH = 'modules/custom/jpf_store';

  /**
   * The initial default letter token identifier.
   */
  private const string DEFAULT_LETTER_IDENTIFIER = 'k';

  /**
   * Get the current version depending on current date.
   *
   * @return self|null
   *   The version.
   */
  public static function currentVersion(): ?self {
    $current_month = DateTimePlus::createFromFormat(
      'U',
      (string) time()
    )->format('Ym');

    foreach (self::cases() as $version) {
      if ($version->begin() <= $current_month && ($version->end() === '' || $version->end() > $current_month)) {
        return $version;
      }
    }

    return NULL;
  }

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
   * Version folder path.
   *
   * @return string
   *   The path of directory.
   */
  public function versionPath(): string {
    return DRUPAL_ROOT . '/' . self::MODULE_PATH . "/assets/doc/$this->value/";
  }

  /**
   * Version file path.
   *
   * @return string
   *   The path of CSV file.
   */
  public function filePath(): string {
    return $this->versionPath() . $this->filename() . self::FILE_EXTENSION;
  }

  /**
   * Version archive path.
   *
   * @return string
   *   The path of archive file.
   */
  public function archivePath(): string {
    return $this->versionPath() . $this->filename() . self::ARCHIVE_EXTENSION;
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

  /**
   * Version letter identifier using in API token.
   *
   * @return string
   *   The letter.
   */
  public function letterIdentifier(): string {
    $default_ascii_code = ord(self::DEFAULT_LETTER_IDENTIFIER);

    return chr($default_ascii_code + $this->versionNumber());
  }

  /**
   * Number of file version.
   *
   * @return int
   *   The number of the version.
   */
  private function versionNumber(): int {
    return match ($this) {
      self::First => 1,
      self::Second => 2,
      self::Third => 3,
      self::Fourth => 4,
      self::Fifth => 5,
    };
  }

}
