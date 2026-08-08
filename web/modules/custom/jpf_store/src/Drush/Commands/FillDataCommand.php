<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Drush\Commands;

use Drupal\drush_batch_bar\Commands\DrushBatchCommands;
use Drupal\jpf_store\Batch\FillDataBatch;
use Drupal\jpf_store\Enum\Versions;
use Drush\Commands\AutowireTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fill data from CSV to DB.
 */
#[AsCommand(
  name: self::NAME,
  description: 'Fill data from CSV to DB.',
  aliases: ['fldd'],
)]
final class FillDataCommand extends Command {

  use AutowireTrait;

  /**
   * The command name.
   */
  public const string NAME = 'fill-lotto-draws-data';

  /**
   * The FillDataCommand constructor.
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
    $this->addOption(
      'loto-versions',
      NULL,
      InputOption::VALUE_OPTIONAL,
      'List of versions to fill, separated with a comma.',
    )
      ->addOption('all', NULL, InputOption::VALUE_NONE, 'Fill all versions.')
      ->addUsage('fill-lotto-draws-data --loto-versions=v1,v2')
      ->addUsage('fldd --all');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $operations = $this->optionsChecker($input->getOption('loto-versions'), $input->getOption('all'), $output);

    if ($operations === NULL) {
      return Command::FAILURE;
    }

    $batch = new DrushBatchCommands(
      operations: FillDataBatch::operations($operations),
      title: 'Import data to database.',
      finished: [
        FillDataBatch::class,
        'finished',
      ],
    );

    $batch->execute();

    return Command::SUCCESS;
  }

  /**
   * Check options and returns versions to fill.
   *
   * @param mixed $versions
   *   List of versions to fill, separated with a comma.
   * @param mixed $all
   *   Fill all versions if TRUE.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   *
   * @return array<string>|null
   *   The options to fill, NULL if error.
   */
  private function optionsChecker(mixed $versions, mixed $all, OutputInterface $output): ?array {
    if (is_string($versions) && $all === TRUE) {
      $this->logger->error('Do not use "versions" and "all" options together.');

      return NULL;
    }

    if ($versions === NULL && $all === FALSE) {
      $this->logger->error('You must use at least one option between "versions" and "all".');

      return NULL;
    }

    if (is_string($versions)) {
      $versions_array = explode(',', $versions);
      $not_allowed_versions = array_diff($versions_array, Versions::values());

      if (!empty($not_allowed_versions)) {
        $items = implode(', ', $not_allowed_versions);

        if (count($not_allowed_versions) === 1) {
          /** @var string $msg */
          $msg = dt('This version is undefined or not allowed: @items', ['@items' => $items]);
          $output->writeln($msg);

          return NULL;
        }

        /** @var string $msg */
        $msg = dt('These versions are undefined or not allowed: @items', ['@items' => $items]);
        $output->writeln($msg);

        return NULL;
      }
    }

    return $versions_array ?? Versions::values();
  }

}
