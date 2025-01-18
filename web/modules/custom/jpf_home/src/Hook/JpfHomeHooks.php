<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for jpf_home.
 */
class JpfHomeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() : array {
    return [
      'homepage' => [
        'variables' => [
          'title' => NULL,
          'description' => NULL,
        ],
        'template' => 'homepage',
      ],
    ];
  }

}
