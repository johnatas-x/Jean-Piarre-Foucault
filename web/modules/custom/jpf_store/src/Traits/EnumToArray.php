<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Traits;

/**
 * Trait for array methods on current Enum.
 */
trait EnumToArray {

  /**
   * Returns an array with all Enum's names.
   *
   * @return array<string>
   *   Enum's names.
   */
  public static function names(): array {
    return array_column(self::cases(), 'name');
  }

  /**
   * Returns an array with all Enum's values.
   *
   * @return array<string>
   *   Enum's values.
   */
  public static function values(): array {
    return array_column(self::cases(), 'value');
  }

  /**
   * Returns an array with all Enum's names => values.
   *
   * @return array<string>
   *   Enum's names and values mapping.
   */
  public static function array(): array {
    return array_combine(self::values(), self::names());
  }

}
