<?php

declare(strict_types=1);

namespace Drupal\jpf_algo\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jpf_utils\Entity\BallEntityBase;

/**
 * Prediction entity.
 */
#[ContentEntityType(
  id: 'prediction',
  label: new TranslatableMarkup('Prediction'),
  label_singular: new TranslatableMarkup('prediction'),
  label_plural: new TranslatableMarkup('predictions'),
  entity_keys: [
    'id' => 'id',
    'version' => 'version',
    'ball_1' => 'ball_1',
    'ball_2' => 'ball_2',
    'ball_3' => 'ball_3',
    'ball_4' => 'ball_4',
    'ball_5' => 'ball_5',
    'ball_6' => 'ball_6',
    'ball_0' => 'ball_0',
  ],
  handlers: [
    'storage' => SqlContentEntityStorage::class,
  ],
  base_table: 'lotto_draws',
)]
class Prediction extends BallEntityBase {
}
