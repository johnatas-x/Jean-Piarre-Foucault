<?php

declare(strict_types=1);

namespace Drupal\jpf_tokens\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\jpf_import\Cron\ImportDynamicData;
use Drupal\jpf_store\Enum\Versions;

/**
 * Hook implementations for jpf_tokens.
 */
class JpfTokensHooks {

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
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
        'last_update' => [
          'name' => t('Last update'),
          'description' => t('Date and time of the last update for the current version.'),
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
  #[Hook('tokens')]
  public function tokens(
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
        'last_update' => ImportDynamicData::lastRun(),
        default => '',
      };
    }

    $bubbleable_metadata->setCacheTags(['custom_tokens']);

    return $replacements;
  }

}
