<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Services;

use Drupal\jpf_store\Enum\Versions;

/**
 * Stats filling methods.
 */
class FillStats implements FillStatsInterface {

  /**
   * {@inheritDoc}
   */
  public function fillBallStats(Versions $version, string $type, int $ball): void {
    // TODO.
  }

}
