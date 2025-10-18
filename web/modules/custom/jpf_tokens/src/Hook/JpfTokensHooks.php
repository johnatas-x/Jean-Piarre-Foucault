<?php

declare(strict_types=1);

namespace Drupal\jpf_tokens\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\jpf_import\Cron\ImportDynamicData;
use Drupal\jpf_store\Enum\Versions;

/**
 * Hook implementations for jpf_tokens.
 */
class JpfTokensHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
    $types = [
      'versions' => [
        'name' => $this->t('Versions tokens'),
        'description' => $this->t('Define custom tokens for versions.'),
      ],
    ];

    $tokens = [
      'versions' => [
        'current_version' => [
          'name' => $this->t('Current version'),
          'description' => $this->t('Current version.'),
        ],
        'start' => [
          'name' => $this->t('Start date'),
          'description' => $this->t('The first day of the current version.'),
        ],
        'last_update' => [
          'name' => $this->t('Last update'),
          'description' => $this->t('Date and time of the last update for the current version.'),
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

    /** @var string[] $tokens */
    foreach ($tokens as $name => $original) {
      $replacements[$original] = match($name) {
        'current_version' => $current_version->value ?? $this->t('unknown version')->render(),
        'start' => $current_version?->humanReadableBeginDate() ?? $this->t('unknown date')->render(),
        'last_update' => ImportDynamicData::lastRun(),
        default => '',
      };
    }

    $bubbleable_metadata->setCacheTags(['custom_tokens']);

    return $replacements;
  }

}
