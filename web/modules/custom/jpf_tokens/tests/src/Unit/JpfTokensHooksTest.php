<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_tokens\Unit;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\jpf_tokens\Hook\JpfTokensHooks;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(JpfTokensHooks::class)]
#[Group('Custom')]
#[TestDox('Tokens: JpfTokensHooks unit')]
final class JpfTokensHooksTest extends UnitTestCase {

  private JpfTokensHooks $hooks;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    if (!defined('ULTIMATE_CRON_LOG_TYPE_NORMAL')) {
      define('ULTIMATE_CRON_LOG_TYPE_NORMAL', 0);
    }

    if (!defined('ULTIMATE_CRON_LOG_TYPE_ALL')) {
      define('ULTIMATE_CRON_LOG_TYPE_ALL', -1);
    }

    $date_formatter = $this->createMock(DateFormatterInterface::class);
    $date_formatter->method('format')->willReturn('6 novembre 2019');

    $cron_storage = $this->createMock(EntityStorageInterface::class);
    $cron_storage->method('load')->willReturn(NULL);

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->method('getStorage')->with('ultimate_cron_job')->willReturn($cron_storage);

    $logger_channel = $this->createMock(LoggerChannelInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')->willReturn($logger_channel);

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('date.formatter', $date_formatter);
    $container->set('entity_type.manager', $entity_type_manager);
    $container->set('logger.factory', $logger_factory);
    \Drupal::setContainer($container);

    $this->hooks = new JpfTokensHooks();
  }

  #[Test]
  #[TestDox('tokenInfo() returns types with a versions key')]
  public function tokenInfoReturnsTypesWithVersionsKey(): void {
    $info = $this->hooks->tokenInfo();

    $this->assertArrayHasKey('versions', $info['types']);
  }

  #[Test]
  #[TestDox('tokenInfo() returns tokens with current_version, start and last_update')]
  public function tokenInfoReturnsExpectedTokenKeys(): void {
    $tokens = $this->hooks->tokenInfo()['tokens']['versions'];

    $this->assertArrayHasKey('current_version', $tokens);
    $this->assertArrayHasKey('start', $tokens);
    $this->assertArrayHasKey('last_update', $tokens);
  }

  #[Test]
  #[TestDox('tokens() returns empty array when type is not versions')]
  public function tokensReturnsEmptyArrayForWrongType(): void {
    $result = $this->hooks->tokens('node', [], [], [], new BubbleableMetadata());

    $this->assertSame([], $result);
  }

  #[Test]
  #[TestDox('tokens() replaces current_version token with current version value')]
  public function tokensReplacesCurrentVersionToken(): void {
    $result = $this->hooks->tokens(
      'versions',
      ['current_version' => '[versions:current_version]'],
      [],
      [],
      new BubbleableMetadata(),
    );

    $this->assertSame('v5', $result['[versions:current_version]']);
  }

  #[Test]
  #[TestDox('tokens() replaces start token with humanReadableBeginDate')]
  public function tokensReplacesStartToken(): void {
    $result = $this->hooks->tokens(
      'versions',
      ['start' => '[versions:start]'],
      [],
      [],
      new BubbleableMetadata(),
    );

    $this->assertSame('6 novembre 2019', $result['[versions:start]']);
  }

  #[Test]
  #[TestDox('tokens() replaces last_update token with ImportDynamicData::lastRun()')]
  public function tokensReplacesLastUpdateToken(): void {
    $result = $this->hooks->tokens(
      'versions',
      ['last_update' => '[versions:last_update]'],
      [],
      [],
      new BubbleableMetadata(),
    );

    $this->assertSame('Unknown', $result['[versions:last_update]']);
  }

  #[Test]
  #[TestDox('tokens() returns empty string for unknown token name')]
  public function tokensReturnsEmptyStringForUnknownToken(): void {
    $result = $this->hooks->tokens(
      'versions',
      ['unknown_token' => '[versions:unknown_token]'],
      [],
      [],
      new BubbleableMetadata(),
    );

    $this->assertSame('', $result['[versions:unknown_token]']);
  }

  #[Test]
  #[TestDox('tokens() sets custom_tokens cache tag on BubbleableMetadata')]
  public function tokensSetsCustomTokensCacheTag(): void {
    $metadata = $this->createMock(BubbleableMetadata::class);
    $metadata->expects($this->once())->method('setCacheTags')->with(['custom_tokens']);

    $this->hooks->tokens('versions', [], [], [], $metadata);
  }

}
