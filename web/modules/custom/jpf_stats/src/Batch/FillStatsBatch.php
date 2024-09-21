<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Batch;

use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_utils\Batch\BaseBatch;

/**
 * Batch methods for FillCommands.
 */
class FillStatsBatch extends BaseBatch {

  /**
   * Batch operations for fill stats drush command.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, string, int, \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   }>
   *   The batch operations.
   */
  public static function operations(Versions $version): array {
    return array_merge(
      self::subOperations($version, 'balls'),
      self::subOperations($version, 'lucky balls')
    );
  }

  /**
   * Generate stats in database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   * @param string $type
   *   Balls type.
   * @param int $ball
   *   The ball number.
   * @param string $details
   *   Details to follow command progress.
   * @param array<mixed> $context
   *   The batch context.
   */
  public static function process(Versions $version, string $type, int $ball, string $details, array &$context): void {
    parent::initProcess($details, $context);

    \Drupal::service('jpf_stats.fill')->fillBallStats($version, $type, $ball);
  }

  /**
   * {@inheritDoc}
   */
  public static function finished(bool $success, array $results, array $operations, string $success_message): void {
    parent::finished($success, $results, $operations, 'stats generated');
  }

  /**
   * Operations maker.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   * @param string $type
   *   The balls type.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, string, int, \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   }>
   *   The batch operations for the given type.
   */
  private static function subOperations(Versions $version, string $type): array {
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
          \Drupal::translation()
            ->translate('Import stats : ball @chunk / @count',
              [
                '@chunk' => $ball,
                '@count' => $ball_max - $ball_min + 1,
              ]
          ),
        ],
      ];
    }

    return $operations;
  }

}
