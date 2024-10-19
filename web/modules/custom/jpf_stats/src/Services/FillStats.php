<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Services;

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $databaseConnection;

  /**
   * The custom database services.
   *
   * @var \Drupal\jpf_store\Services\DatabaseInterface
   */
  protected DatabaseInterface $jpfDatabase;

  /**
   * The FillStats constructor.
   *
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The database connection.
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpf_database
   *   The custom database services.
   */
  public function __construct(Connection $database_connection, DatabaseInterface $jpf_database) {
    $this->databaseConnection = $database_connection;
    $this->jpfDatabase = $jpf_database;
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
    ];

    if ($type === 'balls') {
      $fields['best_friend'] = $this->getBestFriend($ball, $version);
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
    $count_query = $this->jpfDatabase
      ->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, ['id']);

    $this->ballCondition($table, $count_query, $ball);

    $count = $count_query
      ->condition('version', $version->value)
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
    $last_query = $this->jpfDatabase
      ->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, ['year', 'month', 'day']);

    $this->ballCondition($table, $last_query, $ball);

    $last_result = $last_query
      ->condition('version', $version->value)
      ->orderBy('id', 'DESC')
      ->range(0, 1)
      ->execute()
      ?->fetchAssoc();

    return empty($last_result['year']) || empty($last_result['month']) || empty($last_result['day'])
      ? NULL
      : sprintf('%d/%02d/%02d', $last_result['year'], $last_result['month'], $last_result['day']);
  }

  /**
   * Get day(s) on which the given ball is released the most for the given version.
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
    $query = $this->jpfDatabase
      ->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, ['day_of_week'])
      ->condition('version', $version->value);

    $this->ballCondition($table, $query, $ball);

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
   * Get ball(s) that comes out most often with the current ball for the given version.
   *
   * @param int $ball
   *   The current ball.
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return string|null
   *   The ball number. If multiple, balls numbers. If all or not, null.
   */
  private function getBestFriend(int $ball, Versions $version): ?string {
    $query = $this->jpfDatabase
      ->selectLotto()
      ->fields(SchemaInterface::LOTTO_TABLE_ALIAS, Balls::classicBallsColumn())
      ->condition('version', $version->value);

    $this->ballCondition(SchemaInterface::LOTTO_DRAWS_TABLE, $query, $ball);

    $friends = $query->execute()?->fetchAll(\PDO::FETCH_NUM);

    if (empty($friends)) {
      return NULL;
    }

    $counts = array_count_values(
      array_filter(
        array_merge(
          ...$friends
        )
      )
    );
    unset($counts[$ball]);
    $best_friends = array_keys($counts, max($counts), TRUE);

    if (count($best_friends) >= $version->drawnBalls()) {
      return NULL;
    }

    sort($best_friends);

    return implode(', ', $best_friends);
  }

}
