<?php

declare(strict_types=1);

namespace Drupal\jpf_import\Cron;

use Drupal\jpf_import\Api\Sto;
use Drupal\jpf_store\Enum\Versions;
use Drupal\ultimate_cron\CronJobInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Callback classe for cron jobs.
 */
final class ImportDynamicData {

  /**
   * The cron ID.
   */
  private const string CRON_ID = 'import_dynamic_data';

  /**
   * Cron callback.
   *
   * @param \Drupal\ultimate_cron\CronJobInterface $job
   *   The cron job entity.
   *
   * @SuppressWarnings("PHPMD.UnusedFormalParameter")
   */
  public static function import(CronJobInterface $job): void {
    $current_version = Versions::currentVersion();

    if (!$current_version instanceof Versions) {
      return;
    }

    try {
      $archive_path = $current_version->archivePath();
      // Download archive.
      $response = \Drupal::httpClient()->get(
        Sto::buildDownloadUrl($current_version),
        [
          'sink' => $archive_path,
        ]
      );

      if ($response->getStatusCode() !== Response::HTTP_OK) {
        \Drupal::logger('jpf_import')->error('Cannot download file.');

        return;
      }

      // Unzip archive.
      $zip = new \ZipArchive();

      if ($zip->open($archive_path, \ZipArchive::CREATE) === TRUE) {
        $zip->extractTo(
          $current_version->versionPath(),
          [$current_version->filename() . Versions::FILE_EXTENSION]
        );
        $zip->close();
      }

      // Delete archive.
      unlink($archive_path);

      \Drupal::service('jpf_store.database')->importCsvFile($current_version);

      exec('drush fill-lotto-stats');
    }
    catch (\Throwable $exception) {
      \Drupal::logger('jpf_import')->error($exception->getMessage());
    }
  }

  /**
   * Get last end date of the current cron.
   *
   * @return string
   *   The date with long format.
   */
  public static function lastRun(): string {
    try {
      $ultimate_cron_entity = \Drupal::entityTypeManager()
        ->getStorage('ultimate_cron_job')
        ->load(self::CRON_ID);

      if (!$ultimate_cron_entity instanceof CronJobInterface) {
        return t('Unknown')->render();
      }

      $log_entry = $ultimate_cron_entity->loadLatestLogEntry();

      return is_numeric($log_entry->end_time)
        ? \Drupal::service('date.formatter')->format((int) $log_entry->end_time, 'long')
        : t('Never')->render();
    }
    catch (\Throwable $exception) {
      \Drupal::logger('jpf_import')->error($exception->getMessage());

      return t('Unknown')->render();
    }
  }

}
