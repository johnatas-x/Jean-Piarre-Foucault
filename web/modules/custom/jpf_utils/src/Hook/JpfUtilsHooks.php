<?php

declare(strict_types=1);

namespace Drupal\jpf_utils\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for jpf_utils.
 */
class JpfUtilsHooks {

  /**
   * Implements hook_toolbar_alter().
   */
  #[Hook('toolbar_alter')]
  public function toolbarAlter(&$items): void {
    $items['admin_toolbar_tools']['#attached']['library'][] = 'jpf_utils/toolbar';
  }

}
