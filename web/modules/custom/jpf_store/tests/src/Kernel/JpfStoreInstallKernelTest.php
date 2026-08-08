<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Kernel;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Insert;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\DatabaseInterface;
use Drupal\jpf_store\Services\Schema;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\jpf_store\Enum\Versions;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('Custom')]
#[TestDox('Store: Install hooks')]
#[CoversFunction('jpf_store_schema')]
#[CoversFunction('jpf_store_install')]
#[CoversFunction('jpf_store_uninstall')]
#[RunTestsInSeparateProcesses]
final class JpfStoreInstallKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    require_once dirname(__DIR__, 3) . '/jpf_store.install';

    $schema_service = $this->createMock(Schema::class);
    $schema_service->method('lottoDrawsFields')->willReturn([
      'id' => ['type' => 'serial', 'unsigned' => TRUE, 'not null' => TRUE],
    ]);
    $schema_service->method('versionStatsFields')->willReturn([
      'version' => ['type' => 'char', 'length' => 2, 'not null' => TRUE],
    ]);
    $this->container->set(Schema::class, $schema_service);
  }

  #[Test]
  #[TestDox('hook_schema() defines lotto_draws and lotto_versions tables')]
  public function schemaDefinesBothTables(): void {
    $schema = jpf_store_schema();

    $this->assertArrayHasKey(SchemaInterface::LOTTO_DRAWS_TABLE, $schema);
    $this->assertArrayHasKey(SchemaInterface::LOTTO_VERSIONS, $schema);
  }

  #[Test]
  #[TestDox('hook_schema() lotto_draws table has id as primary key')]
  public function schemaDrawsTableHasIdAsPrimaryKey(): void {
    $schema = jpf_store_schema();

    $this->assertSame(['id'], $schema[SchemaInterface::LOTTO_DRAWS_TABLE]['primary key']);
  }

  #[Test]
  #[TestDox('hook_schema() lotto_versions table has version as primary key')]
  public function schemaVersionsTableHasVersionAsPrimaryKey(): void {
    $schema = jpf_store_schema();

    $this->assertSame(['version'], $schema[SchemaInterface::LOTTO_VERSIONS]['primary key']);
  }

  #[Test]
  #[TestDox('hook_install() inserts one row per Versions case')]
  public function installInsertsOneRowPerVersion(): void {
    $insert = $this->createMock(Insert::class);
    $insert->method('fields')->willReturnSelf();
    $insert->method('execute')->willReturn(NULL);

    $connection = $this->createMock(Connection::class);
    $connection->expects($this->exactly(count(Versions::cases())))
      ->method('insert')
      ->with(SchemaInterface::LOTTO_VERSIONS)
      ->willReturn($insert);
    $this->container->set('database', $connection);

    jpf_store_install();
  }

  #[Test]
  #[TestDox('hook_uninstall() calls deleteTable with lotto_draws')]
  public function uninstallCallsDeleteTableWithLottoDraws(): void {
    $database = $this->createMock(DatabaseInterface::class);
    $database->expects($this->once())
      ->method('deleteTable')
      ->with(SchemaInterface::LOTTO_DRAWS_TABLE);
    $this->container->set(Database::class, $database);

    jpf_store_uninstall();
  }

}
