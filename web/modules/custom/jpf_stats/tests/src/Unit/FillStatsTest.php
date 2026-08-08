<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_stats\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\Core\Database\Query\Merge;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Statement\FetchAs;
use Drupal\Core\Database\StatementInterface;
use Drupal\jpf_stats\Services\FillStats;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(FillStats::class)]
#[Group('Custom')]
#[TestDox('Stats: FillStats unit')]
final class FillStatsTest extends UnitTestCase {

  private Connection $databaseConnection;
  private DatabaseInterface $jpfDatabase;
  private FillStats $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->databaseConnection = $this->createMock(Connection::class);
    $this->jpfDatabase = $this->createMock(DatabaseInterface::class);

    $this->service = new FillStats($this->databaseConnection, $this->jpfDatabase);
  }

  /**
   * Builds a fully-stubbed SelectInterface chain where execute() returns null.
   */
  private function buildSelectStub(): SelectInterface {
    $condition = $this->createMock(ConditionInterface::class);
    $condition->method('condition')->willReturnSelf();

    $count_select = $this->createMock(SelectInterface::class);
    $count_select->method('execute')->willReturn(NULL);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orderBy')->willReturnSelf();
    $select->method('range')->willReturnSelf();
    $select->method('addExpression')->willReturnSelf();
    $select->method('groupBy')->willReturnSelf();
    $select->method('countQuery')->willReturn($count_select);
    $select->method('execute')->willReturn(NULL);
    $select->method('orConditionGroup')->willReturn($condition);

    return $select;
  }

  /**
   * Builds a condition mock usable as orConditionGroup() return value.
   */
  private function buildConditionStub(): ConditionInterface {
    $condition = $this->createMock(ConditionInterface::class);
    $condition->method('condition')->willReturnSelf();
    return $condition;
  }

  /**
   * Builds a SelectInterface stub for getCount(): countQuery()->execute()->fetchField() returns $count.
   */
  private function buildCountSelectStub(string $count): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchField')->willReturn($count);

    $count_select = $this->createMock(SelectInterface::class);
    $count_select->method('execute')->willReturn($statement);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orConditionGroup')->willReturn($this->buildConditionStub());
    $select->method('countQuery')->willReturn($count_select);
    return $select;
  }

  /**
   * Builds a SelectInterface stub for getLast(): orderBy()->range()->execute()->fetchAssoc() returns $row.
   *
   * @param array<string, string>|null $row
   *   The row to return.
   */
  private function buildLastSelectStub(?array $row): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchAssoc')->willReturn($row);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orConditionGroup')->willReturn($this->buildConditionStub());
    $select->method('orderBy')->willReturnSelf();
    $select->method('range')->willReturnSelf();
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  /**
   * Builds a SelectInterface stub for getBestDay(): addExpression()->groupBy()->execute()->fetchAllKeyed() returns $keyed.
   *
   * @param array<string, int> $keyed
   *   The day => count map to return.
   */
  private function buildBestDaySelectStub(array $keyed): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchAllKeyed')->willReturn($keyed);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orConditionGroup')->willReturn($this->buildConditionStub());
    $select->method('addExpression')->willReturn('count');
    $select->method('groupBy')->willReturnSelf();
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  /**
   * Builds a SelectInterface stub for getFrequency(): execute()->fetchAll() returns $objects.
   *
   * @param array<\stdClass> $objects
   *   The result rows to return.
   */
  private function buildFrequencySelectStub(array $objects): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchAll')->willReturn($objects);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orConditionGroup')->willReturn($this->buildConditionStub());
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  /**
   * Builds a SelectInterface stub for getFriend(): execute()->fetchAll(FetchAs::List) returns $rows.
   *
   * @param array<array<string>> $rows
   *   The result rows to return.
   */
  private function buildFriendSelectStub(array $rows): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchAll')->willReturn($rows);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orConditionGroup')->willReturn($this->buildConditionStub());
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  /**
   * Builds stdClass date objects for getFrequency().
   *
   * @param array<string> $dates
   *   Dates in Y/m/d format.
   *
   * @return array<\stdClass>
   *   The objects.
   */
  private function buildDateObjects(array $dates): array {
    return array_map(static function (string $date): \stdClass {
      [$year, $month, $day] = explode('/', $date);
      $obj = new \stdClass();
      $obj->year = $year;
      $obj->month = $month;
      $obj->day = $day;
      return $obj;
    }, $dates);
  }

  /**
   * Builds a Merge mock with fluent key() and fields() methods.
   */
  private function buildMergeStub(string $table, int $ball): Merge {
    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->with('ball', $ball)->willReturnSelf();
    $merge->method('fields')->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())->method('merge')->with($table)->willReturn($merge);
    return $merge;
  }

  #[Test]
  #[TestDox('fillBallStats() returns early without merging when total count is zero')]
  public function fillBallStatsReturnsEarlyWhenTotalCountIsZero(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(0);
    $this->databaseConnection->expects($this->never())->method('merge');

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

  #[Test]
  #[TestDox('fillBallStats() for lucky type merges into the lucky stats table')]
  public function fillBallStatsForLuckyTypeMergesIntoLuckyStatsTable(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);
    $this->jpfDatabase->method('selectLotto')->willReturn($this->buildSelectStub());
    $this->buildMergeStub(SchemaInterface::LOTTO_STATS_LUCKY_TABLE, 3);

    $this->service->fillBallStats(Versions::Fifth, 'lucky', 3);
  }

  #[Test]
  #[TestDox('fillBallStats() for balls type merges into the balls stats table')]
  public function fillBallStatsForBallsTypeMergesIntoBallsStatsTable(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);
    $this->jpfDatabase->method('selectLotto')->willReturn($this->buildSelectStub());

    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->with('ball', 5)->willReturnSelf();
    $merge->expects($this->once())->method('fields')
      ->with($this->callback(static fn (array $fields): bool => (
        array_key_exists('best_friend', $fields) && array_key_exists('worst_friend', $fields)
      )))
      ->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())
      ->method('merge')
      ->with(SchemaInterface::LOTTO_STATS_BALLS_TABLE)
      ->willReturn($merge);

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

  #[Test]
  #[TestDox('fillBallStats() for balls type computes count, last, best_day, frequency and friends from real query results')]
  public function fillBallStatsForBallsTypeComputesAllStatsFields(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);

    $friend_rows = [
      ['7', '12', '23', '34', '5', '45'],
      ['7', '12', '5', '34', '42', '45'],
    ];

    $this->jpfDatabase->method('selectLotto')->willReturnOnConsecutiveCalls(
      $this->buildCountSelectStub('3'),
      $this->buildLastSelectStub(['year' => '2023', 'month' => '11', 'day' => '04']),
      $this->buildBestDaySelectStub(['Samedi' => 3, 'Lundi' => 2]),
      $this->buildFrequencySelectStub($this->buildDateObjects(['2023/11/04', '2023/12/09'])),
      $this->buildFriendSelectStub($friend_rows),
      $this->buildFriendSelectStub($friend_rows),
    );

    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->with('ball', 5)->willReturnSelf();
    $merge->expects($this->once())->method('fields')
      ->with($this->callback(static fn (array $fields): bool => (
        $fields['count'] === 3
        && $fields['last'] === '2023/11/04'
        && $fields['best_day'] === 'Saturday'
        && $fields['frequency'] === 35
        && $fields['best_friend'] !== NULL
        && $fields['worst_friend'] !== NULL
      )))
      ->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())
      ->method('merge')
      ->with(SchemaInterface::LOTTO_STATS_BALLS_TABLE)
      ->willReturn($merge);

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

  #[Test]
  #[TestDox('fillBallStats() for balls type sets best_day to null when all days have equal count')]
  public function fillBallStatsForBallsTypeBestDayNullWhenAllDaysEqualCount(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);

    $friend_rows = [
      ['7', '12', '23', '34', '5', '45'],
      ['7', '12', '5', '34', '42', '45'],
    ];

    $this->jpfDatabase->method('selectLotto')->willReturnOnConsecutiveCalls(
      $this->buildCountSelectStub('3'),
      $this->buildLastSelectStub(['year' => '2023', 'month' => '11', 'day' => '04']),
      $this->buildBestDaySelectStub(['Samedi' => 3, 'Lundi' => 3]),
      $this->buildFrequencySelectStub($this->buildDateObjects(['2023/11/04', '2023/12/09'])),
      $this->buildFriendSelectStub($friend_rows),
      $this->buildFriendSelectStub($friend_rows),
    );

    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->with('ball', 5)->willReturnSelf();
    $merge->expects($this->once())->method('fields')
      ->with($this->callback(static fn (array $fields): bool => $fields['best_day'] === NULL))
      ->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())->method('merge')->willReturn($merge);

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

  #[Test]
  #[TestDox('fillBallStats() for balls type sets frequency to null when fewer than two valid dates in results')]
  public function fillBallStatsForBallsTypeFrequencyNullWhenFewerThanTwoValidDates(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);

    $friend_rows = [
      ['7', '12', '23', '34', '5', '45'],
      ['7', '12', '5', '34', '42', '45'],
    ];

    $this->jpfDatabase->method('selectLotto')->willReturnOnConsecutiveCalls(
      $this->buildCountSelectStub('3'),
      $this->buildLastSelectStub(['year' => '2023', 'month' => '11', 'day' => '04']),
      $this->buildBestDaySelectStub(['Samedi' => 3, 'Lundi' => 2]),
      $this->buildFrequencySelectStub([new \stdClass()]),
      $this->buildFriendSelectStub($friend_rows),
      $this->buildFriendSelectStub($friend_rows),
    );

    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->willReturnSelf();
    $merge->expects($this->once())->method('fields')
      ->with($this->callback(static fn (array $fields): bool => $fields['frequency'] === NULL))
      ->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())->method('merge')->willReturn($merge);

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

  #[Test]
  #[TestDox('fillBallStats() for balls type sets friends to null when counts empty or all balls tied')]
  public function fillBallStatsForBallsTypeFriendNullWhenCountsEmptyOrAllTied(): void {
    $this->jpfDatabase->method('getCountRecords')->willReturn(100);

    // best friend (line 313): 5 unique balls all tied → count >= drawnBalls(5) → NULL
    $all_tied_rows = [['7', '12', '23', '34', '5', '45']];
    // worst friend (line 304): all values equal the current ball → counts empty → NULL
    $all_same_rows = [['5', '5', '5', '5', '5', '5']];

    $this->jpfDatabase->method('selectLotto')->willReturnOnConsecutiveCalls(
      $this->buildCountSelectStub('3'),
      $this->buildLastSelectStub(['year' => '2023', 'month' => '11', 'day' => '04']),
      $this->buildBestDaySelectStub(['Samedi' => 3, 'Lundi' => 2]),
      $this->buildFrequencySelectStub($this->buildDateObjects(['2023/11/04', '2023/12/09'])),
      $this->buildFriendSelectStub($all_tied_rows),
      $this->buildFriendSelectStub($all_same_rows),
    );

    $merge = $this->createMock(Merge::class);
    $merge->expects($this->once())->method('key')->willReturnSelf();
    $merge->expects($this->once())->method('fields')
      ->with($this->callback(static fn (array $fields): bool => (
        $fields['best_friend'] === NULL
        && $fields['worst_friend'] === NULL
      )))
      ->willReturnSelf();
    $merge->method('execute')->willReturn(NULL);
    $this->databaseConnection->expects($this->once())->method('merge')->willReturn($merge);

    $this->service->fillBallStats(Versions::Fifth, 'balls', 5);
  }

}
