<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\jpf_store\Enum\Balls;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Balls::class)]
#[Group('Custom')]
#[TestDox('Store: Balls enum unit')]
final class BallsTest extends UnitTestCase {

  /**
   * @return array<string, array{Balls, int}>
   */
  public static function numericProvider(): array {
    return [
      'One' => [Balls::One, 1],
      'Two' => [Balls::Two, 2],
      'Three' => [Balls::Three, 3],
      'Four' => [Balls::Four, 4],
      'Five' => [Balls::Five, 5],
      'Six' => [Balls::Six, 6],
      'Complementary' => [Balls::Complementary, 0],
      'Lucky' => [Balls::Lucky, 0],
    ];
  }

  /**
   * @return array<string, array{Balls, string}>
   */
  public static function csvNameProvider(): array {
    return [
      'One' => [Balls::One, 'boule_1'],
      'Two' => [Balls::Two, 'boule_2'],
      'Three' => [Balls::Three, 'boule_3'],
      'Four' => [Balls::Four, 'boule_4'],
      'Five' => [Balls::Five, 'boule_5'],
      'Six' => [Balls::Six, 'boule_6'],
      'Complementary' => [Balls::Complementary, 'boule_complementaire'],
      'Lucky' => [Balls::Lucky, 'numero_chance'],
    ];
  }

  #[Test]
  #[TestDox('numeric() returns the correct integer for each case')]
  #[DataProvider('numericProvider')]
  public function numericReturnsCorrectInteger(Balls $ball, int $expected): void {
    $this->assertSame($expected, $ball->numeric());
  }

  #[Test]
  #[TestDox('columnName() returns ball_ prefixed with numeric value')]
  public function columnNameReturnsBallPrefixedWithNumeric(): void {
    $this->assertSame('ball_1', Balls::One->columnName());
    $this->assertSame('ball_0', Balls::Complementary->columnName());
    $this->assertSame('ball_0', Balls::Lucky->columnName());
  }

  #[Test]
  #[TestDox('csvName() returns the correct CSV column name')]
  #[DataProvider('csvNameProvider')]
  public function csvNameReturnsCorrectCsvColumnName(Balls $ball, string $expected): void {
    $this->assertSame($expected, $ball->csvName());
  }

  #[Test]
  #[TestDox('classicBalls() returns the 6 classic balls without complementary and lucky')]
  public function classicBallsReturnsSixClassicBalls(): void {
    $classic = Balls::classicBalls();

    $this->assertCount(6, $classic);
    $this->assertNotContains(Balls::Complementary, $classic);
    $this->assertNotContains(Balls::Lucky, $classic);
  }

  #[Test]
  #[TestDox('classicBallsColumn() returns 6 column name strings')]
  public function classicBallsColumnReturnsSixColumnNames(): void {
    $columns = Balls::classicBallsColumn();

    $this->assertCount(6, $columns);
    $this->assertContains('ball_1', $columns);
    $this->assertContains('ball_6', $columns);
    $this->assertNotContains('ball_0', $columns);
  }

  #[Test]
  #[TestDox('Constants have correct values')]
  public function constantsHaveCorrectValues(): void {
    $this->assertSame(1, Balls::BALL_MIN);
    $this->assertSame(49, Balls::BALL_MAX);
    $this->assertSame(1, Balls::LUCKY_MIN);
    $this->assertSame(10, Balls::LUCKY_MAX);
    $this->assertSame('solid rubber', Balls::MATERIAL);
    $this->assertSame(26, Balls::WEIGHT);
    $this->assertSame('g', Balls::WEIGHT_UNIT);
    $this->assertSame(5, Balls::DIAMETER);
    $this->assertSame('cm', Balls::DIAMETER_UNIT);
  }

}
