<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Enum;

use Drupal\jpf_utils\Traits\EnumToArray;

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
  case Lucky = 'Lucky ball';

  /**
   * Smallest possible number for a ball.
   */
  final public const int BALL_MIN = 1;

  /**
   * Largest possible number for a ball.
   */
  final public const int BALL_MAX = 49;

  /**
   * Smallest possible number for a lucky ball.
   */
  final public const int LUCKY_MIN = 1;

  /**
   * Largest possible number for a lucky ball.
   */
  final public const int LUCKY_MAX = 10;

  /**
   * The material of the balls.
   */
  final public const string MATERIAL = 'solid rubber';

  /**
   * The weight of the balls (value + unit).
   */
  final public const int WEIGHT = 26;
  final public const string WEIGHT_UNIT = 'g';

  /**
   * The diameter of the balls (value + unit).
   */
  final public const int DIAMETER = 5;
  final public const string DIAMETER_UNIT = 'cm';

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
      self::Complementary, self::Lucky => 0,
    };
  }

  /**
   * Column name in DB "lotto_draws" table.
   *
   * @return string
   *   The column name.
   */
  public function columnName(): string {
    return 'ball_' . $this->numeric();
  }

  /**
   * Name in FDJ CSV file.
   *
   * @return string
   *   The CSV name.
   */
  public function csvName(): string {
    return match ($this) {
      self::Complementary => 'boule_complementaire',
      self::Lucky => 'numero_chance',
      default => 'boule_' . $this->numeric(),
    };
  }

  /**
   * Get classic balls.
   *
   * @return \Drupal\jpf_store\Enum\Balls[]
   *   Array of balls enum cases.
   */
  public static function classicBalls(): array {
    return [
      self::One,
      self::Two,
      self::Three,
      self::Four,
      self::Five,
      self::Six,
    ];
  }

  /**
   * Get DB column name for each classic ball.
   *
   * @return array<string>
   *   The column names.
   */
  public static function classicBallsColumn(): array {
    return array_map(
      static fn (self $ball) => $ball->columnName(),
      self::classicBalls()
    );
  }

}
