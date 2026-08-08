<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_store\Batch\FillDataBatch;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Enum\Versions;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(FillDataBatch::class)]
#[Group('Custom')]
#[TestDox('Store: FillDataBatch unit')]
final class FillDataBatchTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $database = $this->createMock(DatabaseInterface::class);
    $container = new ContainerBuilder();
    $container->set(Database::class, $database);
    \Drupal::setContainer($container);
  }

  #[Test]
  #[TestDox('operations() returns one operation per version string')]
  public function operationsReturnsOneOperationPerVersion(): void {
    $versions = ['v1', 'v2', 'v5'];

    $operations = FillDataBatch::operations($versions);

    $this->assertCount(3, $operations);
  }

  #[Test]
  #[TestDox('operations() returns empty array when no versions given')]
  public function operationsReturnsEmptyArrayWhenNoVersions(): void {
    $this->assertSame([], FillDataBatch::operations([]));
  }

  #[Test]
  #[TestDox('operations() each operation has the correct callable and Versions enum argument')]
  public function operationsEachOperationHasCorrectStructure(): void {
    $operations = FillDataBatch::operations(['v5']);
    [$callable, $args] = $operations[0];

    $this->assertSame([FillDataBatch::class, 'process'], $callable);
    $this->assertSame(Versions::Fifth, $args[0]);
  }

  #[Test]
  #[TestDox('process() on success increments success counter in context')]
  public function processOnSuccessIncrementsSuccessCounter(): void {
    $context = [];
    FillDataBatch::process(Versions::Fifth, $context);

    $this->assertSame(1, $context['results']['success']);
    $this->assertSame(0, $context['results']['error']);
  }

  #[Test]
  #[TestDox('process() on exception increments error counter and sets message in context')]
  public function processOnExceptionIncrementsErrorCounterAndSetsMessage(): void {
    $database = $this->createMock(DatabaseInterface::class);
    $database->method('importCsvFile')->willThrowException(new \RuntimeException('import error'));
    $container = new ContainerBuilder();
    $container->set(Database::class, $database);
    \Drupal::setContainer($container);

    $context = [];
    FillDataBatch::process(Versions::Fifth, $context);

    $this->assertSame(0, $context['results']['success']);
    $this->assertSame(1, $context['results']['error']);
    $this->assertSame('[KO] import error', $context['message']);
  }

}
