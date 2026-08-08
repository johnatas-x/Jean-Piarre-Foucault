<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_store\Drush\Commands\FillDataCommand;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(FillDataCommand::class)]
#[Group('Custom')]
#[TestDox('Store: FillDataCommand unit')]
final class FillDataCommandTest extends UnitTestCase {

  private FillDataCommand $command;
  private LoggerInterface $logger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    $this->logger = $this->createMock(LoggerInterface::class);
    $this->command = new FillDataCommand($this->logger);
  }

  /**
   * Builds a mocked InputInterface with the given option values.
   *
   * @param mixed $versions
   *   Value for the loto-versions option.
   * @param mixed $all
   *   Value for the all option.
   */
  private function buildInput(mixed $versions, mixed $all): InputInterface {
    $input = $this->createMock(InputInterface::class);
    $input->method('getOption')->willReturnMap([
      ['loto-versions', $versions],
      ['all', $all],
    ]);
    return $input;
  }

  #[Test]
  #[TestDox('NAME constant equals fill-lotto-draws-data')]
  public function nameConstantEqualsFillLottoDrawsData(): void {
    $this->assertSame('fill-lotto-draws-data', FillDataCommand::NAME);
  }

  #[Test]
  #[TestDox('execute() returns FAILURE and logs error when both versions and all are provided')]
  public function executeReturnsFailureWhenBothOptionsProvided(): void {
    $this->logger->expects($this->once())->method('error');
    $output = $this->createMock(OutputInterface::class);

    $result = $this->command->execute($this->buildInput('v5', TRUE), $output);

    $this->assertSame(Command::FAILURE, $result);
  }

  #[Test]
  #[TestDox('execute() returns FAILURE and logs error when no option is provided')]
  public function executeReturnsFailureWhenNoOptionProvided(): void {
    $this->logger->expects($this->once())->method('error');
    $output = $this->createMock(OutputInterface::class);

    $result = $this->command->execute($this->buildInput(NULL, FALSE), $output);

    $this->assertSame(Command::FAILURE, $result);
  }

  #[Test]
  #[TestDox('execute() returns FAILURE and logs error when an unknown version is provided')]
  public function executeReturnsFailureForUnknownVersion(): void {
    $this->logger->expects($this->once())->method('error');
    $output = $this->createMock(OutputInterface::class);

    $result = $this->command->execute($this->buildInput('v99', FALSE), $output);

    $this->assertSame(Command::FAILURE, $result);
  }

}
