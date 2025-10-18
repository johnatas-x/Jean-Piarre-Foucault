<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jpf_utils\Entity\BallEntityBase;

/**
 * Draw entity declaration.
 */
#[ContentEntityType(
  id: 'draw',
  label: new TranslatableMarkup('Draw'),
  label_singular: new TranslatableMarkup('draw'),
  label_plural: new TranslatableMarkup('draws'),
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
  base_table: 'lotto_draws'
)]
class Draw extends BallEntityBase {

}
