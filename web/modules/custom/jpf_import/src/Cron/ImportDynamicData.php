<?php

declare(strict_types=1);

namespace Drupal\jpf_import\Cron;

use Consolidation\SiteAlias\SiteAliasInterface;
use Drupal\jpf_import\Api\Sto;
use Drupal\jpf_store\Enum\Versions;
use Drupal\ultimate_cron\CronJobInterface;
use Drush\Drush;
use Symfony\Component\HttpFoundation\Response;

/**
 * Callback classe for cron jobs.
 */
final class ImportDynamicData {

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

      $site_alias = Drush::service('site.alias.manager')->getSelf();

      if (!$site_alias instanceof SiteAliasInterface) {
        return;
      }

      Drush::drush($site_alias, 'fill-lotto-stats')->run();
    }
    catch (\Throwable $exception) {
      \Drupal::logger('jpf_import')->error($exception->getMessage());
    }
  }

}
