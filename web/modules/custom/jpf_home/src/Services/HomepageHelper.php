<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_utils\Entity\BallEntityBase;

/**
 * Helper methods for homepage.
 */
class HomepageHelper implements HomepageHelperInterface {

  /*
   * PHPCS is not yet 100% compatible with PHP 8.4, so we are forced to ignore
   * the "Property hooks" as long as they are not supported.
   *
   * phpcs:disable
   */

  /**
   * The last record ID (the last draw).
   *
   * @var int|null
   */
  protected private(set) ?int $lastRecordId {
    get => $this->lastRecordId ??= $this->jpfDatabase->getLastRecordId();
  }

  /*
   * Re-enable PHPCS for the rest of the file.
   *
   * phpcs:enable
   */

  /**
   * The HomepageHelper constructor.
   *
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpfDatabase
   *   JPF database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   */
  public function __construct(
    protected DatabaseInterface $jpfDatabase,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelFactoryInterface $logger,
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public function getLastData(string $data_type, string $property = 'id'): array {
    return $this->getData($data_type, $property, $this->lastRecordId);
  }

  /**
   * {@inheritDoc}
   */
  public function nextPrediction(): array {
    return $this->getData('prediction', 'draw_id', $this->lastRecordId + 1);
  }

  /**
   * Get Homepage data.
   *
   * @param string $data_type
   *   The data type.
   * @param string $property
   *   The entity property to load.
   * @param int|null $value
   *   The entity property value to load.
   *
   * @return array{
   *   balls: list<int|null>,
   *   lucky: int|null
   *   }
   *   Data to display.
   */
  private function getData(string $data_type, string $property, ?int $value): array {
    $data = [
      'balls' => [],
      'lucky' => NULL,
    ];

    try {
      $entities = $this->entityTypeManager
        ->getStorage($data_type)
        ->loadByProperties([$property => $value]);

      $entity = reset($entities);

      if ($entity instanceof BallEntityBase) {
        $data['balls'] = $entity->balls();
        $data['lucky'] = $entity->lucky();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $exception) {
      $this->logger->get('jpf_home')->error($exception->getMessage());

      return $data;
    }

    return $data;
  }

}
