<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_algo\Kernel;

use Drupal\jpf_algo\Entity\Prediction;
use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('Custom')]
#[TestDox('Algo: Install hooks')]
#[CoversFunction('jpf_algo_schema')]
#[CoversFunction('jpf_algo_uninstall')]
#[RunTestsInSeparateProcesses]
final class JpfAlgoInstallKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    require_once dirname(__DIR__, 3) . '/jpf_algo.install';
  }

  #[Test]
  #[TestDox('hook_schema() defines lotto_prediction table')]
  public function schemaDefinesLottoPredictionTable(): void {
    $schema = jpf_algo_schema();

    $this->assertArrayHasKey(Prediction::LOTTO_PREDICT_TABLE, $schema);
  }

  #[Test]
  #[TestDox('hook_schema() lotto_prediction table has id, draw_id and version fields')]
  public function schemaTableHasBaseFields(): void {
    $fields = jpf_algo_schema()[Prediction::LOTTO_PREDICT_TABLE]['fields'];

    $this->assertArrayHasKey('id', $fields);
    $this->assertArrayHasKey('draw_id', $fields);
    $this->assertArrayHasKey('version', $fields);
  }

  #[Test]
  #[TestDox('hook_schema() lotto_prediction table has a column for every ball')]
  public function schemaTableHasAllBallFields(): void {
    $fields = jpf_algo_schema()[Prediction::LOTTO_PREDICT_TABLE]['fields'];

    foreach (Balls::cases() as $ball) {
      $this->assertArrayHasKey(
        $ball->columnName(),
        $fields,
        sprintf('Field %s exists.', $ball->columnName()),
      );
    }
  }

  #[Test]
  #[TestDox('hook_schema() lotto_prediction table has id as primary key')]
  public function schemaTableHasIdAsPrimaryKey(): void {
    $schema = jpf_algo_schema()[Prediction::LOTTO_PREDICT_TABLE];

    $this->assertSame(['id'], $schema['primary key']);
  }

  #[Test]
  #[TestDox('hook_schema() lotto_prediction unique keys include draw_id and all ball columns')]
  public function schemaTableUniqueKeysIncludeDrawIdAndAllBalls(): void {
    $unique_cols = jpf_algo_schema()[Prediction::LOTTO_PREDICT_TABLE]['unique keys']['unique_cols'] ?? [];

    $this->assertContains('draw_id', $unique_cols);
    foreach (Balls::cases() as $ball) {
      $this->assertContains($ball->columnName(), $unique_cols);
    }
  }

  #[Test]
  #[TestDox('hook_uninstall() calls deleteTable with lotto_prediction')]
  public function uninstallCallsDeleteTableWithLottoPrediction(): void {
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())
      ->method('deleteTable')
      ->with(Prediction::LOTTO_PREDICT_TABLE);
    $this->container->set(Database::class, $database);

    jpf_algo_uninstall();
  }

}
