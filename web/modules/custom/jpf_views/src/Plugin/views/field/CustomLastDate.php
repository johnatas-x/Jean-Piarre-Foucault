<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Plugin\views\field;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\views\ResultRow;

/**
 * Plugin for translatable custom last date.
 *
 * @ViewsField("custom_last_date")
 */
class CustomLastDate extends CustomFieldBase {

  /**
   * {@inheritDoc}
   */
  protected const string DB_FIELD = 'last';

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values): string {
    return is_string($this->getCurrentValue($values))
      ? \Drupal::service('date.formatter')
        ->format(
        DateTimePlus::createFromFormat(
          'Y/m/d',
          $this->getCurrentValue($values)
        )->getTimestamp(),
        'custom_short_day'
      )
      : t('Unknown')->render();
  }

}
