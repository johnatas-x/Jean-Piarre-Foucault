<?php

declare(strict_types=1);

namespace Drupal\jpf_utils\Batch;

/**
 * Base class for all JPF batches.
 */
abstract class BaseBatch {

  /**
   * Init batch processes.
   *
   * @param string $details
   *   Details to follow command progress.
   * @param array<mixed> $context
   *   The batch context.
   */
  protected static function initProcess(string $details, array &$context): void {
    $context['message'] = "\n$details\n";
    $context['results']['success'] ??= 0;
    $context['results']['error'] ??= 0;
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
   * @param string $success_message
   *   The success message.
   */
  protected static function finished(bool $success, array $results, array $operations, string $success_message): void {
    if ($success === TRUE) {
      \Drupal::messenger()->addStatus(
        \Drupal::translation()->translate(
          '@success @success_message, @error errors.',
          [
            '@success' => $results['success'] ?? 0,
            '@success_message' => $success_message,
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
