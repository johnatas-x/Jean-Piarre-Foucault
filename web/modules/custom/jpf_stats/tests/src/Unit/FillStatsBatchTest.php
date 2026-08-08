<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_stats\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_stats\Batch\FillStatsBatch;
use Drupal\jpf_stats\Services\FillStats;
use Drupal\jpf_stats\Services\FillStatsInterface;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Enum\Versions;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(FillStatsBatch::class)]
#[Group('Custom')]
#[TestDox('Stats: FillStatsBatch unit')]
final class FillStatsBatchTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $fill_stats = $this->createMock(FillStatsInterface::class);
    $container = new ContainerBuilder();
    $container->set(FillStats::class, $fill_stats);
    \Drupal::setContainer($container);
  }

  #[Test]
  #[TestDox('operations() with balls type returns one operation per ball (1 to 49)')]
  public function operationsWithBallsTypeReturnsOneOperationPerBall(): void {
    $operations = FillStatsBatch::operations(Versions::Fifth, 'balls');

    $this->assertCount(Balls::BALL_MAX - Balls::BALL_MIN + 1, $operations);
  }

  #[Test]
  #[TestDox('operations() with lucky type returns one operation per lucky ball (1 to 10)')]
  public function operationsWithLuckyTypeReturnsOneOperationPerLuckyBall(): void {
    $operations = FillStatsBatch::operations(Versions::Fifth, 'lucky');

    $this->assertCount(Balls::LUCKY_MAX - Balls::LUCKY_MIN + 1, $operations);
  }

  #[Test]
  #[TestDox('operations() each operation contains the callable and the correct arguments')]
  public function operationsEachOperationHasCorrectStructure(): void {
    $operations = FillStatsBatch::operations(Versions::Fifth, 'balls');
    [$callable, $args] = $operations[0];

    $this->assertSame([FillStatsBatch::class, 'process'], $callable);
    $this->assertSame(Versions::Fifth, $args[0]);
    $this->assertSame('balls', $args[1]);
    $this->assertSame(Balls::BALL_MIN, $args[2]);
  }

  #[Test]
  #[TestDox('process() on success increments success counter in context')]
  public function processOnSuccessIncrementsSuccessCounter(): void {
    $context = [];
    FillStatsBatch::process(Versions::Fifth, 'balls', 5, $context);

    $this->assertSame(1, $context['results']['success']);
    $this->assertSame(0, $context['results']['error']);
  }

  #[Test]
  #[TestDox('process() on exception increments error counter and sets message in context')]
  public function processOnExceptionIncrementsErrorCounterAndSetsMessage(): void {
    $fill_stats = $this->createMock(FillStatsInterface::class);
    $fill_stats->method('fillBallStats')->willThrowException(new \RuntimeException('stat error'));
    $container = new ContainerBuilder();
    $container->set(FillStats::class, $fill_stats);
    \Drupal::setContainer($container);

    $context = [];
    FillStatsBatch::process(Versions::Fifth, 'balls', 5, $context);

    $this->assertSame(0, $context['results']['success']);
    $this->assertSame(1, $context['results']['error']);
    $this->assertSame('[KO] stat error', $context['message']);
  }

}
