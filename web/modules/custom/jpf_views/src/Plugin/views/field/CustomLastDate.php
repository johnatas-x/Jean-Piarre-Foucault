<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Plugin\views\field;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ResultRow;

/**
 * Plugin for translatable custom last date.
 *
 * @ViewsField("custom_last_date")
 */
class CustomLastDate extends FieldPluginBase {

  /**
   * Field DB name.
   *
   * @var string
   */
  private string $dbField = 'last';

  /**
   * {@inheritDoc}
   */
  public function query(): void {
    if (!$this->query instanceof Sql) {
      return;
    }

    $this->query->addField(NULL, $this->dbField);
  }

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values): string {
    $current_value = $values->{$this->table . '_' . $this->dbField};

    return is_string($current_value)
      ? \Drupal::service('date.formatter')
        ->format(
        DateTimePlus::createFromFormat(
          'Y/m/d',
          $current_value
        )->getTimestamp(),
        'custom_short_day'
      )
      : t('Unknown')->render();
  }

}
