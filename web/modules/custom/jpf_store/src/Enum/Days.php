<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Enum;

use Drupal\jpf_store\Traits\EnumToArray;

/**
 * Days mapping.
 */
enum Days: string {

  use EnumToArray;

  case Monday = 'Monday';
  case Tuesday = 'Tuesday';
  case Wednesday = 'Wednesday';
  case Thursday = 'Thursday';
  case Friday = 'Friday';
  case Saturday = 'Saturday';
  case Sunday = 'Sunday';

  /**
   * Get enum case from value obtained by a method.
   *
   * @param string $method
   *   The method name.
   * @param string|null $value
   *   The method output value.
   *
   * @return self|null
   *   The enum case or NULL if no match found.
   */
  public static function fromMethod(string $method, ?string $value): ?self {
    if (is_string($value) && method_exists(self::class, $method)) {
      foreach (self::cases() as $case) {
        if ($case->$method() === trim($value)) {
          return $case;
        }
      }
    }

    return NULL;
  }

  /**
   * French uppercase day code (used as CSV value).
   *
   * @return string
   *   The uppercase day code.
   */
  public function uppercaseDayCode(): string {
    return strtoupper($this->dayCode());
  }

  /**
   * French uppercase French label.
   *
   * @return string
   *   The uppercase French label.
   */
  public function uppercaseFrenchLabel(): string {
    return strtoupper($this->frenchLabel());
  }

  /**
   * French capitalize French label.
   *
   * @return string
   *   The capitalize French label.
   */
  public function capitalizeFrenchLabel(): string {
    return ucfirst($this->frenchLabel());
  }

  /**
   * French day labels.
   *
   * @return string
   *   The French label.
   */
  private function frenchLabel(): string {
    return match ($this) {
      self::Monday => 'lundi',
      self::Tuesday => 'mardi',
      self::Wednesday => 'mercredi',
      self::Thursday => 'jeudi',
      self::Friday => 'vendredi',
      self::Saturday => 'samedi',
      self::Sunday => 'dimanche',
    };
  }

  /**
   * French day code.
   *
   * @return string
   *   The day code.
   */
  private function dayCode(): string {
    return substr($this->frenchLabel(), 0, 2);
  }

}
