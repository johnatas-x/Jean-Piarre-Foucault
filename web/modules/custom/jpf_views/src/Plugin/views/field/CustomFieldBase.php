<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ResultRow;

/**
 * Base plugin for custom views fields.
 */
abstract class CustomFieldBase extends FieldPluginBase {

  /**
   * Field DB name.
   */
  protected const string DB_FIELD = '';

  /**
   * {@inheritDoc}
   */
  public function query(): void {
    if (!$this->query instanceof Sql) {
      return;
    }

    $this->query->addField(NULL, static::DB_FIELD);
  }

  /**
   * Get current field value.
   *
   * @param \Drupal\views\ResultRow $values
   *   The current values.
   *
   * @return string|null
   *   The current value or null if not a string.
   */
  protected function getCurrentValue(ResultRow $values): ?string {
    $current_value = $values->{$this->table . '_' . static::DB_FIELD};

    return is_string($current_value)
      ? $current_value
      : NULL;
  }

}
