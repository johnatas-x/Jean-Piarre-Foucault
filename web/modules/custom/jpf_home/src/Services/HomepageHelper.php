<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\DatabaseInterface;

/**
 * Helper methods for homepage.
 */
class HomepageHelper implements HomepageHelperInterface {

  /**
   * JPF database service.
   *
   * @var \Drupal\jpf_store\Services\DatabaseInterface
   */
  protected DatabaseInterface $jpfDatabase;

  /**
   * The HomepageHelper constructor.
   *
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpf_database
   *   JPF database service.
   */
  public function __construct(DatabaseInterface $jpf_database) {
    $this->jpfDatabase = $jpf_database;
  }

  /**
   * {@inheritDoc}
   */
  public function getLastDraw(): array {
    $last_draw = [
      'balls' => [],
      'lucky' => NULL,
    ];

    $last_record = $this->jpfDatabase->getLastRecord();
    $balls_number = Versions::currentVersion()?->drawnBalls();

    if (!is_array($last_record) || !is_int($balls_number)) {
      return $last_draw;
    }

    $last_draw['lucky'] = !empty($last_record[Balls::Lucky->columnName()])
      ? (int) $last_record[Balls::Lucky->columnName()]
      : NULL;

    for ($ball_num = 0; $ball_num < $balls_number; $ball_num++) {
      $last_draw['balls'][] = !empty($last_record[Balls::from(Balls::values()[$ball_num])->columnName()])
        ? (int) $last_record[Balls::from(Balls::values()[$ball_num])->columnName()]
        : NULL;
    }

    sort($last_draw['balls']);

    return $last_draw;
  }

}
