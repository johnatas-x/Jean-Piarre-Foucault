<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\views\ViewExecutable;

/**
 * Hook implementations for jpf_views.
 */
class JpfViewsHooks {

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data): void {
    foreach (SchemaInterface::LOTTO_STATS_TABLES as $table_name) {
      $data[$table_name]['custom_last_date'] = [
        'title' => t('Custom last date'),
        'group' => t('Custom Table Views'),
        'field' => [
          'title' => t('Custom last date'),
          'help' => t('Translatable custom last date.'),
          'id' => 'custom_last_date',
        ],
      ];
      $data[$table_name]['custom_best_day'] = [
        'title' => t('Custom best day'),
        'group' => t('Custom Table Views'),
        'field' => [
          'title' => t('Custom best day'),
          'help' => t('Translatable custom best day.'),
          'id' => 'custom_best_day',
        ],
      ];
      $data[$table_name]['delta'] = [
        'title' => t('Delta'),
        'group' => t('Custom Table Views'),
        'field' => [
          'title' => t('Delta'),
          'help' => t('Delta between last and frequency.'),
          'id' => 'delta',
        ],
      ];
    }
  }

  /**
   * Implements hook_views_pre_render().
   */
  #[Hook('views_pre_render')]
  public function viewsPreRender(ViewExecutable $view): void {
    if (!is_string($view->id()) || !str_starts_with($view->id(), 'lotto_stats')) {
      return;
    }

    $view->element['#attached']['library'][] = 'jpf_views/jpf_views';
  }

}
