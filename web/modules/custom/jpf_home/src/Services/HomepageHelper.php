<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jpf_store\Entity\Draw;
use Drupal\jpf_store\Services\DatabaseInterface;

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
  public function getLastData(string $data_type): array {
    $last_data = [
      'balls' => [],
      'lucky' => NULL,
    ];

    try {
      $last_record = $this->entityTypeManager
        ->getStorage($data_type)
        ->load($this->jpfDatabase->getLastRecordId());

      if ($last_record instanceof Draw) {
        $last_data['balls'] = $last_record->balls();
        $last_data['lucky'] = $last_record->lucky();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $exception) {
      \Drupal::logger('jpf_home')->error($exception->getMessage());

      return $last_data;
    }

    return $last_data;
  }

}
