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
  public function getLastDraw(): array {
    $last_draw = [
      'balls' => [],
      'lucky' => NULL,
    ];

    try {
      $last_record = $this->entityTypeManager
        ->getStorage('draw')
        ->load($this->jpfDatabase->getLastRecordId());

      if ($last_record instanceof Draw) {
        $last_draw['balls'] = $last_record->balls();
        $last_draw['lucky'] = $last_record->lucky();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $exception) {
      \Drupal::logger('jpf_home')->error($exception->getMessage());

      return $last_draw;
    }

    return $last_draw;
  }

  /**
   * {@inheritDoc}
   */
  public function getLastPredict(): array {
    $last_predict = [
      'balls' => [],
      'lucky' => NULL,
    ];

    // TODO get last predict in database.

    sort($last_predict['balls']);

    return $last_predict;
  }

}
