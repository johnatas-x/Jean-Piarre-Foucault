<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_stats\Kernel;

use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Services\Schema;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('Custom')]
#[TestDox('Stats: Install hooks')]
#[CoversFunction('jpf_stats_schema')]
#[CoversFunction('jpf_stats_install')]
#[CoversFunction('jpf_stats_uninstall')]
#[RunTestsInSeparateProcesses]
final class JpfStatsInstallKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    require_once dirname(__DIR__, 3) . '/jpf_stats.install';

    $schema_service = $this->createMock(Schema::class);
    $schema_service->method('lottoStatsFields')->willReturn([
      'ball' => ['type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE],
    ]);
    $this->container->set(Schema::class, $schema_service);
  }

  #[Test]
  #[TestDox('hook_schema() defines both stats tables')]
  public function schemaDefinesBothStatsTables(): void {
    $schema = jpf_stats_schema();

    $this->assertArrayHasKey(SchemaInterface::LOTTO_STATS_BALLS_TABLE, $schema);
    $this->assertArrayHasKey(SchemaInterface::LOTTO_STATS_LUCKY_TABLE, $schema);
  }

  #[Test]
  #[TestDox('hook_schema() each table has ball as primary key')]
  public function schemaEachTableHasBallAsPrimaryKey(): void {
    $schema = jpf_stats_schema();

    foreach (SchemaInterface::LOTTO_STATS_TABLES as $table_name) {
      $this->assertSame(['ball'], $schema[$table_name]['primary key'], "Table $table_name primary key.");
    }
  }

  #[Test]
  #[TestDox('hook_schema() each table has fields from lottoStatsFields()')]
  public function schemaEachTableHasFieldsFromLottoStatsFields(): void {
    $schema = jpf_stats_schema();

    foreach (SchemaInterface::LOTTO_STATS_TABLES as $table_name) {
      $this->assertArrayHasKey('ball', $schema[$table_name]['fields'], "Table $table_name has ball field.");
    }
  }

  #[Test]
  #[TestDox('hook_install() returns early when balls table does not exist')]
  public function installReturnsEarlyWhenBallsTableDoesNotExist(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->with(SchemaInterface::LOTTO_STATS_BALLS_TABLE)->willReturn(FALSE);
    $db_schema->expects($this->never())->method('addField');

    $connection = $this->createMock(\Drupal\Core\Database\Connection::class);
    $connection->method('schema')->willReturn($db_schema);
    $this->container->set('database', $connection);

    jpf_stats_install();
  }

  #[Test]
  #[TestDox('hook_install() adds missing friend columns when table exists')]
  public function installAddsMissingFriendColumnsWhenTableExists(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->with(SchemaInterface::LOTTO_STATS_BALLS_TABLE)->willReturn(TRUE);
    $db_schema->method('fieldExists')->willReturn(FALSE);
    $db_schema->expects($this->exactly(2))->method('addField');

    $connection = $this->createMock(\Drupal\Core\Database\Connection::class);
    $connection->method('schema')->willReturn($db_schema);
    $this->container->set('database', $connection);

    jpf_stats_install();
  }

  #[Test]
  #[TestDox('hook_install() skips addField when columns already exist')]
  public function installSkipsAddFieldWhenColumnsAlreadyExist(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->with(SchemaInterface::LOTTO_STATS_BALLS_TABLE)->willReturn(TRUE);
    $db_schema->method('fieldExists')->willReturn(TRUE);
    $db_schema->expects($this->never())->method('addField');

    $connection = $this->createMock(\Drupal\Core\Database\Connection::class);
    $connection->method('schema')->willReturn($db_schema);
    $this->container->set('database', $connection);

    jpf_stats_install();
  }

  #[Test]
  #[TestDox('hook_uninstall() returns early when first table does not exist')]
  public function uninstallReturnsEarlyWhenFirstTableDoesNotExist(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->willReturn(FALSE);

    $connection = $this->createMock(\Drupal\Core\Database\Connection::class);
    $connection->method('schema')->willReturn($db_schema);
    $this->container->set('database', $connection);

    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->never())->method('deleteTable');
    $this->container->set(Database::class, $database);

    jpf_stats_uninstall();
  }

  #[Test]
  #[TestDox('hook_uninstall() calls deleteTable when table exists')]
  public function uninstallCallsDeleteTableWhenTableExists(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->willReturn(TRUE);

    $connection = $this->createMock(\Drupal\Core\Database\Connection::class);
    $connection->method('schema')->willReturn($db_schema);
    $this->container->set('database', $connection);

    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->exactly(2))->method('deleteTable');
    $this->container->set(Database::class, $database);

    jpf_stats_uninstall();
  }

}
