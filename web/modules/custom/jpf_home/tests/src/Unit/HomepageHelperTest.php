<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_home\Unit;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\jpf_home\Services\HomepageHelper;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_utils\Entity\BallEntityBase;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(HomepageHelper::class)]
#[Group('Custom')]
#[TestDox('Home: HomepageHelper unit')]
final class HomepageHelperTest extends UnitTestCase {

  private DatabaseInterface $jpfDatabase;
  private EntityTypeManagerInterface $entityTypeManager;
  private LoggerChannelFactoryInterface $loggerFactory;
  private HomepageHelper $helper;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->jpfDatabase = $this->createMock(DatabaseInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);

    $this->helper = new HomepageHelper(
      $this->jpfDatabase,
      $this->entityTypeManager,
      $this->loggerFactory,
    );
  }

  /**
   * Builds a BallEntityBase mock returning the given balls and lucky number.
   *
   * @param list<int|null> $balls
   *   Ball numbers.
   * @param int|null $lucky
   *   Lucky ball number.
   */
  private function buildEntityMock(array $balls, ?int $lucky): BallEntityBase {
    $entity = $this->createMock(BallEntityBase::class);
    $entity->method('balls')->willReturn($balls);
    $entity->method('lucky')->willReturn($lucky);
    return $entity;
  }

  /**
   * Configures entityTypeManager to return a storage mock for the given entity type.
   *
   * @param array<object> $entities
   *   Entities returned by loadByProperties.
   */
  private function mockStorage(string $entity_type, array $entities): EntityStorageInterface {
    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->willReturn($entities);
    $this->entityTypeManager->method('getStorage')
      ->with($entity_type)
      ->willReturn($storage);
    return $storage;
  }

  #[Test]
  #[TestDox('getLastData() returns balls and lucky from the loaded entity')]
  public function getLastDataReturnsBallsAndLuckyFromLoadedEntity(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(10);
    $entity = $this->buildEntityMock([5, 12, 23, 34, 45], 7);
    $this->mockStorage('draw', [$entity]);

    $result = $this->helper->getLastData('draw');

    $this->assertSame([5, 12, 23, 34, 45], $result['balls']);
    $this->assertSame(7, $result['lucky']);
  }

  #[Test]
  #[TestDox('getLastData() loads entity using the given property')]
  public function getLastDataLoadsEntityUsingGivenProperty(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(5);
    $entity = $this->buildEntityMock([1, 2, 3, 4, 5], 3);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->expects($this->once())
      ->method('loadByProperties')
      ->with(['draw_id' => 5])
      ->willReturn([$entity]);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $this->helper->getLastData('prediction', 'draw_id');
  }

  #[Test]
  #[TestDox('getLastData() returns empty data when no entity is found')]
  public function getLastDataReturnsEmptyDataWhenNoEntityFound(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(99);
    $this->mockStorage('draw', []);

    $result = $this->helper->getLastData('draw');

    $this->assertSame([], $result['balls']);
    $this->assertNull($result['lucky']);
  }

  #[Test]
  #[TestDox('getLastData() caches lastRecordId across multiple calls')]
  public function getLastDataCachesLastRecordId(): void {
    $this->jpfDatabase->expects($this->once())
      ->method('getLastRecordId')
      ->willReturn(3);

    $draw_storage = $this->createMock(EntityStorageInterface::class);
    $draw_storage->method('loadByProperties')->willReturn([]);
    $predict_storage = $this->createMock(EntityStorageInterface::class);
    $predict_storage->method('loadByProperties')->willReturn([]);

    $this->entityTypeManager->method('getStorage')
      ->willReturnCallback(fn (string $type) => match ($type) {
        'draw' => $draw_storage,
        'prediction' => $predict_storage,
      });

    $this->helper->getLastData('draw');
    $this->helper->getLastData('prediction', 'draw_id');
  }

  #[Test]
  #[TestDox('nextPrediction() loads entity with lastRecordId + 1 as draw_id')]
  public function nextPredictionLoadsEntityWithLastRecordIdPlusOne(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(10);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->expects($this->once())
      ->method('loadByProperties')
      ->with(['draw_id' => 11])
      ->willReturn([]);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $this->helper->nextPrediction();
  }

  #[Test]
  #[TestDox('nextPrediction() returns empty data when no entity is found')]
  public function nextPredictionReturnsEmptyDataWhenNoEntityFound(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(10);
    $this->mockStorage('prediction', []);

    $result = $this->helper->nextPrediction();

    $this->assertSame([], $result['balls']);
    $this->assertNull($result['lucky']);
  }

  #[Test]
  #[TestDox('getLastData() logs error and returns empty on InvalidPluginDefinitionException')]
  public function getLastDataLogsErrorOnInvalidPluginDefinitionException(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(1);

    $logger = $this->createMock(LoggerChannelInterface::class);
    $logger->expects($this->once())->method('error');
    $this->loggerFactory->method('get')->with('jpf_home')->willReturn($logger);

    $this->entityTypeManager->method('getStorage')
      ->willThrowException(new InvalidPluginDefinitionException('draw'));

    $result = $this->helper->getLastData('draw');

    $this->assertSame([], $result['balls']);
    $this->assertNull($result['lucky']);
  }

  #[Test]
  #[TestDox('getLastData() logs error and returns empty on PluginNotFoundException')]
  public function getLastDataLogsErrorOnPluginNotFoundException(): void {
    $this->jpfDatabase->method('getLastRecordId')->willReturn(1);

    $logger = $this->createMock(LoggerChannelInterface::class);
    $logger->expects($this->once())->method('error');
    $this->loggerFactory->method('get')->with('jpf_home')->willReturn($logger);

    $this->entityTypeManager->method('getStorage')
      ->willThrowException(new PluginNotFoundException('draw'));

    $result = $this->helper->getLastData('draw');

    $this->assertSame([], $result['balls']);
    $this->assertNull($result['lucky']);
  }

}
