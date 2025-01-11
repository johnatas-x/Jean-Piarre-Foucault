<?php

declare(strict_types=1);

namespace Drupal\jpf_views\Plugin\views\field;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\views\ResultRow;

/**
 * Plugin for delta.
 *
 * @ViewsField("delta")
 */
class Delta extends CustomFieldBase {

  /**
   * {@inheritDoc}
   */
  protected const array QUERY_DB_FIELDS = [
    'frequency',
    'last',
  ];

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values): ?string {
    $current_values = $this->getCurrentValues($values);

    if (!is_string($current_values['last'])) {
      return NULL;
    }

    $last = DateTimePlus::createFromFormat('Y/m/d', $current_values['last']);
    $today = DateTimePlus::createFromFormat(
      'U',
      (string) time()
    );

    $diff = $last->diff($today)->days;

    return (string) ($diff - (int) $current_values['frequency']);
  }

}
