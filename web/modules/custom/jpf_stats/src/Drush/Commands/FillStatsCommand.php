<?php

declare(strict_types=1);

namespace Drupal\jpf_stats\Drush\Commands;

use Drupal\drush_batch_bar\Commands\DrushBatchCommands;
use Drupal\jpf_stats\Batch\FillStatsBatch;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\SchemaInterface;
use Drush\Commands\AutowireTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fill stats in DB.
 */
#[AsCommand(
  name: self::NAME,
  description: 'Fill stats in DB.',
  aliases: ['fls'],
)]
final class FillStatsCommand extends Command {

  use AutowireTrait;

  /**
   * The command name.
   */
  public const string NAME = 'fill-lotto-stats';

  /**
   * The FillStatsCommand constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    private readonly LoggerInterface $logger,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this->addUsage('fill-lotto-stats');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $version = Versions::currentVersion();

    if (!$version instanceof Versions) {
      $this->logger->error('Invalid current version.');

      return Command::FAILURE;
    }

    foreach (array_keys(SchemaInterface::LOTTO_STATS_TABLES) as $type) {
      $batch = new DrushBatchCommands(
        operations: FillStatsBatch::operations($version, $type),
        title: "Fill stats in database for $type.",
        finished: [
          FillStatsBatch::class,
          'finished',
        ],
      );

      $batch->execute();
    }

    return Command::SUCCESS;
  }

}
