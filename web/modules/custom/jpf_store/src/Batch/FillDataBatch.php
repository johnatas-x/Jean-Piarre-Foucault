<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Batch;

use Drupal\jpf_store\Enum\Versions;

/**
 * Batch methods for FillCommands.
 */
class FillDataBatch {

  /**
   * Batch operations for fill data drush command.
   *
   * @param array<string> $versions
   *   Versions to import.
   *
   * @return array<int<0, max>, array{
   *   array{class-string, 'process'},
   *   array{\Drupal\jpf_store\Enum\Versions, \Drupal\Core\StringTranslation\TranslatableMarkup}
   *   }>
   *   The batch operations.
   */
  public static function operations(array $versions): array {
    $operations = [];
    $increment = 1;

    foreach ($versions as $version) {
      $operations[] = [
        [self::class, 'process'],
        [
          Versions::from($version),
          \Drupal::translation()
            ->translate('Import data : version @chunk / @count',
              ['@chunk' => $increment, '@count' => count($versions)]
          ),
        ],
      ];

      $increment++;
    }

    return $operations;
  }

  /**
   * Import data to database.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   * @param string $details
   *   Details to follow command progress.
   * @param array<mixed> $context
   *   The batch context.
   */
  public static function process(Versions $version, string $details, array &$context): void {
    $context['message'] = "\n$details\n";

    if (!isset($context['results']['success'])) {
      $context['results']['success'] = 0;
    }

    if (!isset($context['results']['error'])) {
      $context['results']['error'] = 0;
    }

    try {
      \Drupal::service('jpf_store.database')->importCsvFile($version);
      $context['results']['success']++;
      $context['message'] = '[OK] ' . $version->filename();
    }
    catch (\Throwable $exception) {
      $context['results']['error']++;
      $context['message'] = '[KO] ' . $exception->getMessage();
    }
  }

  /**
   * Custom function to run at the end of treatment.
   *
   * @param bool $success
   *   Success.
   * @param array<string, int> $results
   *   Results.
   * @param array<int, array{0: callable, 1: array<int, mixed>}> $operations
   *   Operations launched.
   */
  public static function finished(bool $success, array $results, array $operations): void {
    if ($success === TRUE) {
      \Drupal::messenger()->addStatus(
        \Drupal::translation()->translate(
          '@success versions imported, @error errors.',
          [
            '@success' => $results['success'] ?? 0,
            '@error' => $results['error'] ?? 0,
          ]
        )
      );

      return;
    }

    $error_operation = reset($operations);

    if (!is_array($error_operation)) {
      \Drupal::messenger()->addError(\Drupal::translation()->translate('An unknown error occurred.'));

      return;
    }

    \Drupal::messenger()->addError(
      \Drupal::translation()->translate(
        'An error occurred during process of @operation with args : @args',
        [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[1], TRUE),
        ]
      )
    );
  }

}
