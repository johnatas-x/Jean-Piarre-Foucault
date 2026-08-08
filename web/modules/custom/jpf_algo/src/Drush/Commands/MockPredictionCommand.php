<?php

declare(strict_types=1);

namespace Drupal\jpf_algo\Drush\Commands;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\jpf_algo\Entity\Prediction;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drush\Commands\AutowireTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Mock a prediction in the DB.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
#[AsCommand(
  name: self::NAME,
  description: 'Mock a prediction in the DB.',
  aliases: ['mockpred'],
)]
final class MockPredictionCommand extends Command {

  use AutowireTrait;
  use StringTranslationTrait;

  /**
   * The command name.
   */
  public const string NAME = 'mock-prediction';

  /**
   * Allowed types for mocked predictions.
   */
  private const array ALLOWED_TYPES = ['last', 'next', 'both'];

  /**
   * The MockPredictionCommand constructor.
   *
   * @param \Drupal\jpf_store\Services\DatabaseInterface $jpfDatabase
   *   JPF database service.
   * @param \Drupal\Core\Database\Connection $databaseConnection
   *   The database connection.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    #[Autowire(service: Database::class)]
    private readonly DatabaseInterface $jpfDatabase,
    private readonly Connection $databaseConnection,
    private readonly LoggerInterface $logger,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this->addArgument('type', InputArgument::OPTIONAL, 'Prediction type (last, next, both).')
      ->addUsage('mock-prediction next')
      ->addUsage('mockpred both');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $type = $input->getArgument('type');

    if (!in_array($type, self::ALLOWED_TYPES, TRUE)) {
      $types = implode(', ', self::ALLOWED_TYPES);
      $this->logger->error("Invalid type. Please use one of these allowed types : $types.");

      return Command::FAILURE;
    }

    $success = match ($type) {
      'last' => $this->mock($this->jpfDatabase->getLastRecordId()),
      'next' => $this->mock(),
      default => $this->mock($this->jpfDatabase->getLastRecordId()) && $this->mock(),
    };

    if ($success === FALSE) {
      return Command::FAILURE;
    }

    $this->logger->notice($this->t('@type prediction(s) successfully mocked.', ['@type' => ucfirst($type)])->render());
    Cache::invalidateTags(['homepage_data']);

    return Command::SUCCESS;
  }

  /**
   * Insert the mock into the DB.
   *
   * @param int|null $draw_id
   *   The draw ID linked to the prediction.
   *
   * @throws \Exception
   */
  private function mock(?int $draw_id = NULL): bool {
    $current_version = Versions::currentVersion();

    if (!$current_version instanceof Versions) {
      $this->logger->error('Invalid current version.');

      return FALSE;
    }

    $random_balls = [];
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

    $this->databaseConnection
      ->insert(Prediction::LOTTO_PREDICT_TABLE)
      ->fields($fields)
      ->execute();

    return TRUE;
  }

}
