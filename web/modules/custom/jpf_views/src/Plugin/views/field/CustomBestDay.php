<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Plugin\views\field;

use Drupal\jpf_utils\Enum\Days;
use Drupal\views\ResultRow;

/**
 * Plugin for translatable custom best day.
 *
 * @ViewsField("custom_best_day")
 */
class CustomBestDay extends CustomFieldBase {

  /**
   * {@inheritDoc}
   */
  protected const string DB_FIELD = 'best_day';

  /**
   * ID for French language.
   */
  private const string FR_LANG_ID = 'fr';

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values): ?string {
    $current_value = $this->getCurrentValue($values);
    $current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if (!is_string($current_value) || $current_language !== self::FR_LANG_ID) {
      return $current_value;
    }

    $fr_days = [];
    $days = explode(' or ', $current_value);

    foreach ($days as $day) {
      $fr_days[] = Days::from($day)->capitalizeFrenchLabel();
    }

    array_filter($fr_days);

    return implode(' ou ', $fr_days);
  }

}
