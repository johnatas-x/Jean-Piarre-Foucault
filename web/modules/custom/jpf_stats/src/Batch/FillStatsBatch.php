<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Batch;

use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;

/**
 * Batch methods for FillCommands.
 */
class FillStatsBatch {

  /**
   * Batch operations for fill stats drush command.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The current version.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, string, \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   }>
   *   The batch operations.
   */
  public static function operations(Versions $version): array {
    $operations = [];

    // Classic balls.
    for ($ball = Balls::BALL_MIN; $ball <= Balls::BALL_MAX; $ball++) {
      $operations[] = [
        [self::class, 'process'],
        [
          $version,
          'balls',
          \Drupal::translation()
            ->translate('Import stats : ball @chunk / @count',
              [
                '@chunk' => $ball,
                '@count' => Balls::BALL_MAX - Balls::BALL_MIN + 1,
              ]
          ),
        ],
      ];
    }

    // Lucky balls.
    for ($lucky = Balls::LUCKY_MIN; $lucky <= Balls::LUCKY_MAX; $lucky++) {
      $operations[] = [
        [self::class, 'process'],
        [
          $version,
          'lucky balls',
          \Drupal::translation()
            ->translate('Import stats : lucky ball @chunk / @count',
              [
                '@chunk' => $lucky,
                '@count' => Balls::LUCKY_MIN - Balls::LUCKY_MAX + 1,
              ]
          ),
        ],
      ];
    }

    return $operations;
  }

}
