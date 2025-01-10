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
   *
   * @var string[]
   */
  protected const array QUERY_DB_FIELDS = [];

  /**
   * DB single field.
   */
  protected readonly string $single;

  /**
   * Constructs a CustomFieldBase object.
   *
   * @param array<mixed> $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    [$this->single] = static::QUERY_DB_FIELDS;
  }

  /**
   * {@inheritDoc}
   */
  public function query(): void {
    if (!$this->query instanceof Sql) {
      return;
    }

    foreach (static::QUERY_DB_FIELDS as $field) {
      $this->query->addField(NULL, $field);
    }
  }

  /**
   * Get current field value.
   *
   * @param \Drupal\views\ResultRow $values
   *   The current values.
   * @param string $field
   *   The current field.
   *
   * @return string|null
   *   The current value or null if not a string.
   */
  protected function getCurrentValue(ResultRow $values, string $field): ?string {
    $current_value = $values->{$this->table . '_' . $field};

    return is_string($current_value)
      ? $current_value
      : NULL;
  }

  /**
   * Get current field values.
   *
   * @param \Drupal\views\ResultRow $values
   *   The current values.
   *
   * @return array<string, string|null>
   *   The current values or null if not a string.
   */
  protected function getCurrentValues(ResultRow $values): array {
    return array_combine(
      static::QUERY_DB_FIELDS,
      array_map(
        fn ($field) => $this->getCurrentValue($values, $field),
        static::QUERY_DB_FIELDS
      )
    );
  }

}
