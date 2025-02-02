<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\Sql\Query;
use Drupal\jpf_algo\Entity\Prediction;
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

  /**
   * {@inheritDoc}
   */
  public function nextPrediction(): array {
    $next_predict = [
      'balls' => [],
      'lucky' => NULL,
    ];

    try {
      $entity_storage = $this->entityTypeManager->getStorage('prediction');
      $prediction_query = $entity_storage->getQuery();

      if (!$prediction_query instanceof Query) {
        throw new \RuntimeException(
          t('Error during prediction query.')->render()
        );
      }

      $prediction_id = $prediction_query->accessCheck()->notExists('draw_id')->execute();

      if (empty($prediction_id) || !is_array($prediction_id)) {
        throw new \RuntimeException(
          t('No existing prediction.')->render()
        );
      }

      $prediction = $entity_storage->load(reset($prediction_id));

      if ($prediction instanceof Prediction) {
        $next_predict['balls'] = $prediction->balls();
        $next_predict['lucky'] = $prediction->lucky();
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException | \RuntimeException $exception) {
      \Drupal::logger('jpf_home')->error($exception->getMessage());

      return $next_predict;
    }

    return $next_predict;
  }

}
