<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

/**
 * Interface for homepage helper service.
 */
interface HomepageHelperInterface {

  /**
   * Get last draw (balls + lucky).
   *
   * @return array{
   *   balls: list<int|null>,
   *   lucky: int|null
   *   }
   */
  public function getLastDraw(): array;

  /**
   * Get last prediction (balls + lucky).
   *
   * @return array{
   *   balls: list<int|null>,
   *   lucky: int|null
   *   }
   */
  public function getLastPredict(): array;

}
