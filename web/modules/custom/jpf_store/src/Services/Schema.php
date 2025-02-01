<?php

namespace Drupal\jpf_store\Services;

use Drupal\jpf_store\Enum\Balls;

/**
 * Schema definitions.
 *
 * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
 */
class Schema implements SchemaInterface {

  /**
   * {@inheritDoc}
   */
  public function lottoDrawsFields(): array {
    $fields = [
      'id' => self::SERIAL_DEFINITION,
      'version' => self::VERSION_DEFINITION,
      'year' => [
        'description' => 'Year',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 4,
      ],
      'month' => [
        'description' => 'Month',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 2,
      ],
      'day' => [
        'description' => 'Day',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 2,
      ],
      'which_draw' => [
        'description' => 'First or second draw',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 1,
      ],
      'day_of_week' => [
        'description' => 'Day of week',
        'type' => 'char',
        'length' => 8,
        'not null' => TRUE,
      ],
    ];

    foreach (Balls::cases() as $ball) {
      $fields[$ball->columnName()] = [
        'description' => $ball->value,
        'type' => 'int',
        'unsigned' => TRUE,
        'length' => 2,
      ];
    }

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function lottoStatsFields(): array {
    return [
      'ball' => [
        'description' => 'Ball',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 2,
      ],
      'count' => [
        'description' => 'Count',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'length' => 4,
      ],
      'percentage' => [
        'description' => 'Percentage',
        'type' => 'numeric',
        'unsigned' => TRUE,
        'not null' => FALSE,
        'precision' => 6,
        'scale' => 4,
      ],
      'last' => [
        'description' => 'Last',
        'type' => 'char',
        'length' => 10,
        'not null' => FALSE,
      ],
      'best_day' => [
        'description' => 'Day on which this ball is released the most',
        'type' => 'char',
        'length' => 64,
        'not null' => FALSE,
      ],
      'frequency' => [
        'description' => 'Average number of days between two outings',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => FALSE,
        'length' => 3,
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function versionStatsFields(): array {
    return [
      'version' => self::VERSION_DEFINITION,
      'draws_count' => [
        'description' => 'Draws count',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => FALSE,
        'length' => 5,
      ],
    ];
  }

}
