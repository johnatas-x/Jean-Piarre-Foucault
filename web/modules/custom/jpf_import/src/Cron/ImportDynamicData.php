<?php

declare(strict_types=1);

namespace Drupal\jpf_import\Cron;

use Drupal\ultimate_cron\CronJobInterface;

/**
 * Callback classe for cron jobs.
 */
final class ImportDynamicData {

  /**
   * Cron callback.
   *
   * @param \Drupal\ultimate_cron\CronJobInterface $job
   *   The cron job entity.
   */
  public static function import(CronJobInterface $job): void {
    // TODO implements.
  }

}
