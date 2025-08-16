<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Services;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\jpf_utils\Enum\Days;

/**
 * Stats filling methods.
 */
class FillStats implements FillStatsInterface {

  /**
   * Minimum number of outputs to calculate frequency.
   */
  private const int OUT_MIN = 2;

  /**
   * Friends constants.
   */
  private const bool WORST_FRIEND = FALSE;
  private const bool BEST_FRIEND = TRUE;

  /**
   * The FillStats constructor.
   *
   * @param \Drupal\Core\Database\Connection $databaseConnection
   *   The database connection.
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpfDatabase
   *   The custom database services.
   */
  public function __construct(
    protected Connection $databaseConnection,
    protected DatabaseInterface $jpfDatabase,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function fillBallStats(Versions $version, string $type, int $ball): void {
    $total_count = $this->jpfDatabase->getCountRecords($version);

    if ($total_count === 0) {
      return;
    }

    $table = $type === 'balls'
      ? SchemaInterface::LOTTO_STATS_BALLS_TABLE
      : SchemaInterface::LOTTO_STATS_LUCKY_TABLE;

    $count = $this->getCount($table, $ball, $version);

    $fields = [
      'count' => $count,
      'percentage' => $count / $total_count * 100,
      'last' => $this->getLast($table, $ball, $version),
      'best_day' => $this->getBestDay($table, $ball, $version),
      'frequency' => $this->getFrequency($table, $ball, $version),
    ];

    if ($type === 'balls') {
      $fields['best_friend'] = $this->getFriend($ball, $version, self::BEST_FRIEND);
      $fields['worst_friend'] = $this->getFriend($ball, $version, self::WORST_FRIEND);
    }

    $this->databaseConnection->merge($table)
      ->key('ball', $ball)
      ->fields($fields)
      ->execute();
  }

  /**
   * Ball sub condition.
   *
   * @param string $table
   *   The DB table.
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The current query.
   * @param int $ball
   *   The current ball.
   */
  private function ballCondition(string $table, SelectInterface $query, int $ball): void {
    if ($table === SchemaInterface::LOTTO_STATS_LUCKY_TABLE) {
      $query->condition(Balls::Lucky->columnName(), $ball);

      return;
    }

    $ball_condition = $query->orConditionGroup();

    foreach (Balls::classicBalls() as $classic_ball) {
      $ball_condition->condition($classic_ball->columnName(), $ball);
    }

    $query->condition($ball_condition);
  }

  /**
   * Get ball count for given version.
   *
   * @param string $table
   *   The ball table.
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return int
   *   Count of draws.
   */
  private function getCount(string $table, int $ball, Versions $version): int {
    $count_query = $this->baseSelectQuery($table, $ball, $version, ['id']);
    $count = $count_query
      ->countQuery()
      ->execute()
      ?->fetchField();

    return is_numeric($count)
      ? (int) $count
      : 0;
  }

  /**
   * Get last draw for the given ball and the given version.
   *
   * @param string $table
   *   The ball table.
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return string|null
   *   The last draw or null if this ball has never been drawn.
   */
  private function getLast(string $table, int $ball, Versions $version): ?string {
    $last_query = $this->baseSelectQuery($table, $ball, $version, ['year', 'month', 'day']);
    $last_result = $last_query
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchAssoc();

    if (
      isset($last_result['year'], $last_result['month'], $last_result['day']) &&
      is_numeric($last_result['year']) &&
      is_numeric($last_result['month']) &&
      is_numeric($last_result['day'])
    ) {
      $year = (int) $last_result['year'];
      $month = (int) $last_result['month'];
      $day = (int) $last_result['day'];

      return sprintf('%d/%02d/%02d', $year, $month, $day);
    }

    return NULL;
  }

  /**
   * Get day(s) when the given ball is most released for the given version.
   *
   * @param string $table
   *   The ball table.
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return string|null
   *   The day name. If multiple, days. If all, null.
   */
  private function getBestDay(string $table, int $ball, Versions $version): ?string {
    $query = $this->baseSelectQuery($table, $ball, $version, ['day_of_week']);
    $query->addExpression('count(day_of_week)', 'count');
    $query->groupBy('lotto.day_of_week');
    $counts = $query->execute()?->fetchAllKeyed();

    if (empty($counts)) {
      return NULL;
    }

    $days = array_filter(array_map(
        static fn (string $value) => Days::fromMethod('capitalizeFrenchLabel', $value)?->value,
        array_keys($counts, max($counts), TRUE)
    ));

    if (empty($days) || count($days) === count($counts)) {
      return NULL;
    }

    return implode(' or ', $days);
  }

  /**
   * Average days between two outings for the given ball and version.
   *
   * @param string $table
   *   The ball table.
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return int|null
   *   The average number of days, null if less than two outings.
   */
  private function getFrequency(string $table, int $ball, Versions $version): ?int {
    $query = $this->baseSelectQuery($table, $ball, $version, ['year', 'month', 'day']);
    $results = $query->execute()?->fetchAll();

    if (empty($results)) {
      return NULL;
    }

    $dates = [];

    foreach ($results as $result) {
      if (
        !isset($result->year, $result->month, $result->day) ||
        !is_numeric($result->year) || !is_numeric($result->month) || !is_numeric($result->day)
      ) {
        continue;
      }

      $dates[] = implode(
        '/',
        [
          (string) $result->year,
          sprintf('%02d', (string) $result->month),
          sprintf('%02d', (string) $result->day),
        ]
      );
    }

    if (count($dates) < self::OUT_MIN) {
      return NULL;
    }

    sort($dates);

    $total_days = 0;
    $count = count($dates) - 1;

    for ($increment = 0; $increment < $count; $increment++) {
      $date1 = DateTimePlus::createFromFormat('Y/m/d', $dates[$increment]);
      $date2 = DateTimePlus::createFromFormat('Y/m/d', $dates[$increment + 1]);
      $interval = $date1->diff($date2);
      $total_days += $interval->days;
    }

    return (int) round($total_days / $count);
  }

  /**
   * Get ball(s) appearing most/least with current ball for given version.
   *
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   * @param bool $type
   *   Type of friend. True for best, false for worst.
   *
   * @return string|null
   *   The ball number. If multiple, balls numbers. If all or not, null.
   */
  private function getFriend(int $ball, Versions $version, bool $type): ?string {
    $query = $this->baseSelectQuery(SchemaInterface::LOTTO_DRAWS_TABLE, $ball, $version, Balls::classicBallsColumn());
    $friends = $query->execute()?->fetchAll(\PDO::FETCH_NUM);

    if (empty($friends)) {
      return NULL;
    }

    $counts = array_count_values(
      array_filter(
        array_merge(
          ...array_map(static fn ($friend) => is_array($friend)
            ? $friend
            : [], $friends)
        )
      )
    );
    unset($counts[$ball]);

    if (empty($counts)) {
      return NULL;
    }

    $filter_value = $type === TRUE
      ? max($counts)
      : min($counts);
    $needed_friends = array_keys($counts, $filter_value, TRUE);

    if (count($needed_friends) >= $version->drawnBalls()) {
      return NULL;
    }

    sort($needed_friends);

    return implode(', ', $needed_friends);
  }

  /**
   * Base of all select queries.
   *
   * @param string $table
   *   The ball table.
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   * @param array<string> $fields
   *   Fields to select.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The initialized query.
   */
  private function baseSelectQuery(string $table, int $ball, Versions $version, array $fields): SelectInterface {
    $query = $this->jpfDatabase
      ->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, $fields)
      ->condition('version', $version->value);

    $this->ballCondition($table, $query, $ball);

    return $query;
  }

}
