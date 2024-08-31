<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Enum;

use Drupal\jpf_store\Traits\EnumToArray;

/**
 * Available balls.
 */
enum Balls: string {

  use EnumToArray;

  case One = 'Ball one';
  case Two = 'Ball two';
  case Three = 'Ball three';
  case Four = 'Ball four';
  case Five = 'Ball five';
  case Six = 'Ball six';
  case Complementary = 'Complementary ball';

  /**
   * Numeric ball value.
   *
   * @return int
   *   The number of ball row (0 for complementary).
   */
  public function numeric(): int {
    return match ($this) {
      self::One => 1,
      self::Two => 2,
      self::Three => 3,
      self::Four => 4,
      self::Five => 5,
      self::Six => 6,
      self::Complementary => 0,
    };
  }

}
