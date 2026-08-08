<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_algo\Unit;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_algo\Drush\Commands\MockPredictionCommand;
use Drupal\jpf_algo\Entity\Prediction;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(MockPredictionCommand::class)]
#[Group('Custom')]
#[TestDox('Algo: MockPredictionCommand unit')]
final class MockPredictionCommandTest extends UnitTestCase {

  private MockPredictionCommand $command;
  private DatabaseInterface $jpfDatabase;
  private Connection $databaseConnection;
  private LoggerInterface $logger;
  private CacheTagsInvalidatorInterface $cacheTagsInvalidator;
  private Insert $insert;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->cacheTagsInvalidator = $this->createMock(CacheTagsInvalidatorInterface::class);
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('cache_tags.invalidator', $this->cacheTagsInvalidator);
    \Drupal::setContainer($container);

    $this->insert = $this->createMock(Insert::class);
    $this->insert->method('fields')->willReturnSelf();
    $this->insert->method('execute')->willReturn('1');

    $this->jpfDatabase = $this->createMock(DatabaseInterface::class);
    $this->databaseConnection = $this->createMock(Connection::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->command = new MockPredictionCommand(
      $this->jpfDatabase,
      $this->databaseConnection,
      $this->logger,
    );
  }

  /**
   * Builds an InputInterface mock returning the given type argument.
   */
  private function buildInput(?string $type): InputInterface {
    $input = $this->createMock(InputInterface::class);
    $input->method('getArgument')->with('type')->willReturn($type);
    return $input;
  }

  /**
   * Builds an OutputInterface mock.
   */
  private function buildOutput(): OutputInterface {
    return $this->createMock(OutputInterface::class);
  }

  /**
   * Configures the database connection mock to expect a given number of insert calls.
   */
  private function expectDbInsert(int $times = 1): void {
    $this->databaseConnection
      ->expects($this->exactly($times))
      ->method('insert')
      ->with(Prediction::LOTTO_PREDICT_TABLE)
      ->willReturn($this->insert);
  }

  #[Test]
  #[TestDox('NAME constant equals mock-prediction')]
  public function nameConstantEqualsMockPrediction(): void {
    $this->assertSame('mock-prediction', MockPredictionCommand::NAME);
  }

  #[Test]
  #[TestDox('execute() with invalid type logs error and returns failure')]
  public function executeWithInvalidTypeLogsErrorAndReturnsFailure(): void {
    $this->logger->expects($this->once())
      ->method('error')
      ->with('Invalid type. Please use one of these allowed types : last, next, both.');

    $result = $this->command->execute($this->buildInput('invalid'), $this->buildOutput());

    $this->assertSame(Command::FAILURE, $result);
  }

  #[Test]
  #[TestDox('execute() with null type returns failure')]
  public function executeWithNullTypeReturnsFailure(): void {
    $result = $this->command->execute($this->buildInput(NULL), $this->buildOutput());

    $this->assertSame(Command::FAILURE, $result);
  }

  #[Test]
  #[TestDox('execute() with type next inserts one prediction and returns success')]
  public function executeWithTypeNextInsertsPredictionAndReturnsSuccess(): void {
    $this->expectDbInsert(1);

    $result = $this->command->execute($this->buildInput('next'), $this->buildOutput());

    $this->assertSame(Command::SUCCESS, $result);
  }

  #[Test]
  #[TestDox('execute() with type last calls getLastRecordId and returns success')]
  public function executeWithTypeLastUsesLastRecordIdAndReturnsSuccess(): void {
    $this->jpfDatabase->expects($this->once())
      ->method('getLastRecordId')
      ->willReturn(42);
    $this->expectDbInsert(1);

    $result = $this->command->execute($this->buildInput('last'), $this->buildOutput());

    $this->assertSame(Command::SUCCESS, $result);
  }

  #[Test]
  #[TestDox('execute() with type both inserts two predictions and returns success')]
  public function executeWithTypeBothInsertsTwoPredictionsAndReturnsSuccess(): void {
    $this->jpfDatabase->expects($this->once())
      ->method('getLastRecordId')
      ->willReturn(7);
    $this->expectDbInsert(2);

    $result = $this->command->execute($this->buildInput('both'), $this->buildOutput());

    $this->assertSame(Command::SUCCESS, $result);
  }

  #[Test]
  #[TestDox('execute() on success logs a notice')]
  public function executeOnSuccessLogsNotice(): void {
    $this->expectDbInsert(1);
    $this->logger->expects($this->once())->method('notice');

    $this->command->execute($this->buildInput('next'), $this->buildOutput());
  }

  #[Test]
  #[TestDox('execute() on success invalidates homepage_data cache tag')]
  public function executeOnSuccessInvalidatesHomepageDataCacheTag(): void {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['homepage_data']);
    $this->expectDbInsert(1);

    $this->command->execute($this->buildInput('next'), $this->buildOutput());
  }

}
