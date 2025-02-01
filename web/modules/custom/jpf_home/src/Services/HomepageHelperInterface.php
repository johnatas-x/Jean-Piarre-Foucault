<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

/**
 * Interface for homepage helper service.
 */
interface HomepageHelperInterface {

  /**
   * Get last data (balls + lucky).
   *
   * @param string $data_type
   *   The data type (draw, prediction).
   *
   * @return array{
   *   balls: list<int|null>,
   *   lucky: int|null
   *   }
   */
  public function getLastData(string $data_type): array;

}
