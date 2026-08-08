<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_import\Unit;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\jpf_import\Cron\ImportDynamicData;
use Drupal\Tests\UnitTestCase;
use Drupal\ultimate_cron\CronJobInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(ImportDynamicData::class)]
#[Group('Custom')]
#[TestDox('Import: ImportDynamicData unit')]
final class ImportDynamicDataTest extends UnitTestCase {

  private EntityTypeManagerInterface $entityTypeManager;
  private DateFormatterInterface $dateFormatter;
  private LoggerChannelInterface $loggerChannel;
  private EntityStorageInterface $cronStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // CronJobInterface uses these global constants as default parameter values.
    if (!defined('ULTIMATE_CRON_LOG_TYPE_NORMAL')) {
      define('ULTIMATE_CRON_LOG_TYPE_NORMAL', 0);
    }

    if (!defined('ULTIMATE_CRON_LOG_TYPE_ALL')) {
      define('ULTIMATE_CRON_LOG_TYPE_ALL', -1);
    }

    $this->loggerChannel = $this->createMock(LoggerChannelInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')->willReturn($this->loggerChannel);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->dateFormatter = $this->createMock(DateFormatterInterface::class);

    $this->cronStorage = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeManager->method('getStorage')
      ->with('ultimate_cron_job')
      ->willReturn($this->cronStorage);

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('date.formatter', $this->dateFormatter);
    $container->set('logger.factory', $logger_factory);
    \Drupal::setContainer($container);
  }

  #[Test]
  #[TestDox('lastRun() returns Unknown when cron entity is not found')]
  public function lastRunReturnsUnknownWhenCronEntityNotFound(): void {
    $this->cronStorage->method('load')->with('import_dynamic_data')->willReturn(NULL);

    $result = ImportDynamicData::lastRun();

    $this->assertSame('Unknown', $result);
  }

  #[Test]
  #[TestDox('lastRun() returns Never when log entry end_time is not numeric')]
  public function lastRunReturnsNeverWhenEndTimeIsNotNumeric(): void {
    $log_entry = new \stdClass();
    $log_entry->end_time = 'not-a-number';

    $cron_job = $this->createMock(CronJobInterface::class);
    $cron_job->method('loadLatestLogEntry')->willReturn($log_entry);
    $this->cronStorage->method('load')->willReturn($cron_job);

    $result = ImportDynamicData::lastRun();

    $this->assertSame('Never', $result);
  }

  #[Test]
  #[TestDox('lastRun() returns formatted date when log entry end_time is numeric')]
  public function lastRunReturnsFormattedDateWhenEndTimeIsNumeric(): void {
    $log_entry = new \stdClass();
    $log_entry->end_time = '1234567890';

    $cron_job = $this->createMock(CronJobInterface::class);
    $cron_job->method('loadLatestLogEntry')->willReturn($log_entry);
    $this->cronStorage->method('load')->willReturn($cron_job);

    $this->dateFormatter->method('format')
      ->with(1234567890, 'long')
      ->willReturn('February 13, 2009');

    $result = ImportDynamicData::lastRun();

    $this->assertSame('February 13, 2009', $result);
  }

  #[Test]
  #[TestDox('lastRun() logs error and returns Unknown on exception')]
  public function lastRunLogsErrorAndReturnsUnknownOnException(): void {
    $this->cronStorage->method('load')
      ->willThrowException(new \RuntimeException('Storage failure'));

    $this->loggerChannel->expects($this->once())
      ->method('error')
      ->with('Storage failure');

    $result = ImportDynamicData::lastRun();

    $this->assertSame('Unknown', $result);
  }

}
