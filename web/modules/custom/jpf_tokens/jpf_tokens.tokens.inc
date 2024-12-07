<?php

/**
 * @file
 * Contains custom tokens.
 */

declare(strict_types=1);

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\jpf_store\Enum\Versions;

/**
 * Implements hook_token_info().
 */
function jpf_tokens_token_info(): array {
  $types = [
    'versions' => [
      'name' => t('Versions tokens'),
      'description' => t('Define custom tokens for versions.'),
    ],
  ];

  $tokens = [
    'versions' => [
      'current_version' => [
        'name' => t('Current version'),
        'description' => t('Current version.'),
      ],
      'start' => [
        'name' => t('Start date'),
        'description' => t('The first day of the current version.'),
      ],
    ],
  ];

  return [
    'types' => $types,
    'tokens' => $tokens,
  ];
}

/**
 * Implements hook_tokens().
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
function jpf_tokens_tokens(
  string $type,
  array $tokens,
  array $data,
  array $options,
  BubbleableMetadata $bubbleable_metadata,
): array {
  $replacements = [];

  if ($type !== 'versions') {
    return $replacements;
  }

  $current_version = Versions::currentVersion();

  foreach ($tokens as $name => $original) {
    $replacements[$original] = match($name) {
      'current_version' => $current_version->value ?? t('unknown version')->render(),
      'start' => $current_version?->humanReadableBeginDate() ?? t('unknown date')->render(),
      default => '',
    };
  }

  return $replacements;
}
