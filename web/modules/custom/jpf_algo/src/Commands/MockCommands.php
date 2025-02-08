<?php

declare(strict_types=1);

namespace Drupal\jpf_algo\Commands;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\jpf_algo\Entity\Prediction;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands to mock predictions.
 */
class MockCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Allowed types for mocked predictions.
   */
  private const array ALLOWED_TYPES = [
    'last',
    'next',
    'both',
  ];

  /**
   * The MockCommands constructor.
   *
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpfDatabase
   *   JPF database service.
   * @param \Drupal\Core\Database\Connection $databaseConnection
   *   The database connection.
   */
  public function __construct(
    protected DatabaseInterface $jpfDatabase,
    protected Connection $databaseConnection,
  ) {
    parent::__construct();
  }

  /**
   * Mock a prediction in the DB.
   *
   * @param string|null $type
   *   The prediction type.
   *   Only 3 values are authorized : last, next, both.
   *
   * @command mock-prediction
   *
   * @aliases mockpred
   *
   * @usage drush mock-prediction next
   *   Mock the next prediction.
   * @usage drush mockpred both
   *   Mock the last and the next prediction.
   *
   * @throws \Exception
   */
  public function mockPrediction(?string $type = NULL): void {
    if (!in_array($type, self::ALLOWED_TYPES, TRUE)) {
      throw new \InvalidArgumentException(
        t(
          'Invalid type. Please use one of these allowed types : @types.',
          ['@types' => implode(', ', self::ALLOWED_TYPES)]
        )->render()
      );
    }

    switch ($type) {
      case 'last':
        $this->mock($this->jpfDatabase->getLastRecordId());
        $this->io()->success(t('Last prediction successfully mocked.')->render());

        break;

      case 'next':
        $this->mock();
        $this->io()->success(t('Next prediction successfully mocked.')->render());

        break;

      default:
        $this->mock($this->jpfDatabase->getLastRecordId());
        $this->mock();
        $this->io()->success(t('Both predictions successfully mocked.')->render());

        break;
    }

    Cache::invalidateTags(['homepage_data']);
  }

  /**
   * Insert the mock into the DB.
   *
   * @param int|null $draw_id
   *   The draw ID linked to the prediction.
   *
   * @throws \Exception
   */
  private function mock(?int $draw_id = NULL): void {
    $current_version = Versions::currentVersion();
    $random_balls = [];

    if (!$current_version instanceof Versions) {
      throw new \RuntimeException(
        t('Invalid current version.')->render()
      );
    }

    $fields = [
      'draw_id' => $draw_id,
      'version' => $current_version->value,
      Balls::Lucky->columnName() => random_int(Balls::LUCKY_MIN, Balls::LUCKY_MAX),
    ];

    foreach (Balls::classicBalls() as $ball) {
      if (count($random_balls) === $current_version->drawnBalls()) {
        break;
      }

      do {
        $random_value = random_int(Balls::BALL_MIN, Balls::BALL_MAX);
      } while (in_array($random_value, $random_balls, TRUE));

      $random_balls[] = $random_value;
      $fields[$ball->columnName()] = $random_value;
    }

    $this->databaseConnection->insert(Prediction::LOTTO_PREDICT_TABLE)
      ->fields($fields)
      ->execute();
  }

}
