<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_utils\Entity\BallEntityBase;

/**
 * Helper methods for homepage.
 */
class HomepageHelper implements HomepageHelperInterface {

  /**
   * The HomepageHelper constructor.
   *
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpfDatabase
   *   JPF database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(
    protected DatabaseInterface $jpfDatabase,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function getLastData(string $data_type, string $property = 'id'): array {
    $last_data = [
      'balls' => [],
      'lucky' => NULL,
    ];

    try {
      $last_record = $this->entityTypeManager
        ->getStorage($data_type)
        ->loadByProperties([$property => $this->jpfDatabase->getLastRecordId()]);

      $balls_entity = reset($last_record);

      if ($balls_entity instanceof BallEntityBase) {
        $last_data['balls'] = $balls_entity->balls();
        $last_data['lucky'] = $balls_entity->lucky();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $exception) {
      \Drupal::logger('jpf_home')->error($exception->getMessage());

      return $last_data;
    }

    return $last_data;
  }

}
