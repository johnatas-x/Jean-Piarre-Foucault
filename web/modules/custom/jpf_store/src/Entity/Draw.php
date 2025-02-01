<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;

/**
 * Draw entity.
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
  base_table: 'lotto_draws',
)]
class Draw extends ContentEntityBase {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Version'))
      ->setRequired(TRUE);

    foreach (Balls::cases() as $ball) {
      $fields[$ball->columnName()] = BaseFieldDefinition::create('integer')
        ->setLabel(t($ball->value))
        ->setRequired(FALSE);
    }

    return $fields;
  }

  /**
   * Get the number of the draw's lucky ball.
   *
   * @return int|null
   *   The number, NULL if empty.
   */
  public function lucky(): ?int {
    return $this->intValue(Balls::Lucky->columnName());
  }

  /**
   * Get the draw's balls number.
   *
   * @return list<int|null>
   *   The balls.
   */
  public function balls(): array {
    $balls = [];
    $balls_number = Versions::from($this->get('version')->getString())->drawnBalls();

    for ($ball_num = 0; $ball_num < $balls_number; $ball_num++) {
      $balls[] = $this->intValue(Balls::from(Balls::values()[$ball_num])->columnName());
    }

    sort($balls);

    return $balls;
  }

  /**
   * Check if value exists and return as integer.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return int|null
   *   The int value or NULL.
   */
  private function intValue(string $field_name): ?int {
    return $this->hasField($field_name) && !empty($this->get($field_name)->getString())
      ? (int) $this->get($field_name)->getString()
      : NULL;
  }

}
