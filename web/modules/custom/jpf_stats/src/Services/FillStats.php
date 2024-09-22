<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Services\SchemaInterface;

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

    $this->databaseConnection->merge($table)
      ->key('ball', $ball)
      ->fields([
        'count' => $count,
        'percentage' => $count / $total_count * 100,
        'last' => $this->getLast($table, $ball, $version),
      ])
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
    $count_query = $this->databaseConnection
      ->select(SchemaInterface::LOTTO_DRAWS_TABLE, 'lotto')
      ->fields('lotto', ['id']);

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
    $last_query = $this->databaseConnection
      ->select(SchemaInterface::LOTTO_DRAWS_TABLE, 'lotto')
      ->fields('lotto', ['year', 'month', 'day']);

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

}
