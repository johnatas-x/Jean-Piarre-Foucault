<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Services;

use Drupal\jpf_store\Enum\Versions;

/**
 * Provides an interface for stats filling methods.
 */
interface FillStatsInterface {

  /**
   * Fill stats in database for current ball & version.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   * @param string $type
   *   Balls type.
   * @param int $ball
   *   The ball number.
   */
  public function fillBallStats(Versions $version, string $type, int $ball): void;

}
