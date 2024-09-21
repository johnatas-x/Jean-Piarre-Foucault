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
      'id' => [
        'description' => 'The primary identifier.',
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ],
      'version' => [
        'description' => 'Version',
        'type' => 'char',
        'not null' => TRUE,
        'length' => 2,
      ],
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
      $fields["ball_{$ball->numeric()}"] = [
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
        'not null' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function versionStatsFields(): array {
    return [
      'version' => [
        'description' => 'Version',
        'type' => 'char',
        'not null' => TRUE,
        'length' => 2,
      ],
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
