<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Batch;

use Drupal\drush_batch_bar\Batch\DrushBatchBar;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;

/**
 * Batch methods for FillCommands.
 */
class FillStatsBatch extends DrushBatchBar {

  /**
   * The finished success message.
   */
  protected const string SUCCESS_MESSAGE = 'stats generated';

  /**
   * Operation maker.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   * @param string $type
   *   The balls type.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, string, int}
   *   }>
   *   The batch operations for the given type.
   */
  public static function operations(Versions $version, string $type): array {
    $operations = [];

    $ball_min = $type === 'balls'
      ? Balls::BALL_MIN
      : Balls::LUCKY_MIN;
    $ball_max = $type === 'balls'
      ? Balls::BALL_MAX
      : Balls::LUCKY_MAX;

    for ($ball = $ball_min; $ball <= $ball_max; $ball++) {
      $operations[] = [
        [self::class, 'process'],
        [
          $version,
          $type,
          $ball,
        ],
      ];
    }

    return $operations;
  }

  /**
   * Generate stats in the database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   * @param string $type
   *   Balls type.
   * @param int $ball
   *   The ball number.
   * @param array<mixed> $context
   *   The batch context.
   */
  public static function process(Versions $version, string $type, int $ball, array &$context): void {
    parent::initProcess($context);

    try {
      \Drupal::service('jpf_stats.fill')->fillBallStats($version, $type, $ball);
      $context['results']['success']++;
    }
    catch (\Throwable $exception) {
      $context['results']['error']++;
      $context['message'] = '[KO] ' . $exception->getMessage();
    }
  }

}
