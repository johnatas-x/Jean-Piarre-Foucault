<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Update;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\CsvHelperInterface;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Database::class)]
#[Group('Custom')]
#[TestDox('Store: Database service unit')]
final class DatabaseTest extends UnitTestCase {

  private Connection $connection;
  private CsvHelperInterface $csvHelper;
  private SchemaInterface $schema;
  private ModuleHandlerInterface $moduleHandler;
  private Database $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->connection = $this->createMock(Connection::class);
    $this->csvHelper = $this->createMock(CsvHelperInterface::class);
    $this->schema = $this->createMock(SchemaInterface::class);
    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);

    $this->service = new Database(
      $this->csvHelper,
      $this->connection,
      $this->schema,
      $this->moduleHandler,
    );
  }

  /**
   * Builds a stubbed SelectInterface where all chain methods return $this and execute() returns null.
   */
  private function buildSelectStub(): SelectInterface {
    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orderBy')->willReturnSelf();
    $select->method('range')->willReturnSelf();
    $select->method('execute')->willReturn(NULL);
    return $select;
  }

  /**
   * Builds a SelectInterface stub that returns $fetchResult from execute()->fetchAssoc().
   *
   * @param mixed $fetchResult
   *   The value to return from fetchAssoc().
   */
  private function buildSelectStubWithFetchAssoc(mixed $fetchResult): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchAssoc')->willReturn($fetchResult);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('orderBy')->willReturnSelf();
    $select->method('range')->willReturnSelf();
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  /**
   * Builds a SelectInterface stub that returns $fetchResult from execute()->fetchField().
   *
   * @param mixed $fetchResult
   *   The value to return from fetchField().
   */
  private function buildSelectStubWithFetchField(mixed $fetchResult): SelectInterface {
    $statement = $this->createMock(StatementInterface::class);
    $statement->method('fetchField')->willReturn($fetchResult);

    $select = $this->createMock(SelectInterface::class);
    $select->method('fields')->willReturnSelf();
    $select->method('condition')->willReturnSelf();
    $select->method('orderBy')->willReturnSelf();
    $select->method('range')->willReturnSelf();
    $select->method('execute')->willReturn($statement);
    return $select;
  }

  #[Test]
  #[TestDox('selectLotto() calls connection->select() with correct table and alias')]
  public function selectLottoCallsConnectionSelectWithCorrectArgs(): void {
    $select = $this->createMock(SelectInterface::class);
    $this->connection->expects($this->once())
      ->method('select')
      ->with(SchemaInterface::LOTTO_DRAWS_TABLE, SchemaInterface::LOTTO_TABLE_ALIAS)
      ->willReturn($select);

    $result = $this->service->selectLotto();

    $this->assertSame($select, $result);
  }

  #[Test]
  #[TestDox('getLastRecord() returns null when execute() returns null')]
  public function getLastRecordReturnsNullWhenExecuteReturnsNull(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStub());

    $this->assertNull($this->service->getLastRecord());
  }

  #[Test]
  #[TestDox('getLastRecord() returns false when fetchAssoc() returns false')]
  public function getLastRecordReturnsFalseWhenFetchAssocReturnsFalse(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStubWithFetchAssoc(FALSE));

    $this->assertFalse($this->service->getLastRecord());
  }

  #[Test]
  #[TestDox('getLastRecord() returns validated array when fetchAssoc() returns a valid record')]
  public function getLastRecordReturnsValidatedArrayForValidRecord(): void {
    $record = ['id' => '42', 'version' => 'v5'];
    $this->connection->method('select')->willReturn($this->buildSelectStubWithFetchAssoc($record));

    $this->assertSame($record, $this->service->getLastRecord());
  }

  #[Test]
  #[TestDox('getLastRecord() throws UnexpectedValueException when record has non-string key')]
  public function getLastRecordThrowsOnNonStringKey(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStubWithFetchAssoc([0 => 'value']));

    $this->expectException(\UnexpectedValueException::class);
    $this->service->getLastRecord();
  }

  #[Test]
  #[TestDox('getLastRecordId() returns null when execute() returns null')]
  public function getLastRecordIdReturnsNullWhenExecuteReturnsNull(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStub());

    $this->assertNull($this->service->getLastRecordId());
  }

  #[Test]
  #[TestDox('getLastRecordId() returns int when fetchField() returns a numeric string')]
  public function getLastRecordIdReturnsIntForNumericResult(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStubWithFetchField('42'));

    $this->assertSame(42, $this->service->getLastRecordId());
  }

  #[Test]
  #[TestDox('deleteTable() does not drop when table does not exist')]
  public function deleteTableDoesNotDropWhenTableDoesNotExist(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->willReturn(FALSE);
    $db_schema->expects($this->never())->method('dropTable');
    $this->connection->method('schema')->willReturn($db_schema);

    $this->service->deleteTable('some_table');
  }

  #[Test]
  #[TestDox('deleteTable() calls dropTable when table exists')]
  public function deleteTableCallsDropTableWhenTableExists(): void {
    $db_schema = $this->createMock(\Drupal\Core\Database\Schema::class);
    $db_schema->method('tableExists')->willReturn(TRUE);
    $db_schema->expects($this->once())->method('dropTable')->with('some_table');
    $this->connection->method('schema')->willReturn($db_schema);

    $this->service->deleteTable('some_table');
  }

  #[Test]
  #[TestDox('getCountRecords() returns 0 when execute() returns null')]
  public function getCountRecordsReturnsZeroWhenExecuteReturnsNull(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStub());

    $this->assertSame(0, $this->service->getCountRecords(Versions::Fifth));
  }

  #[Test]
  #[TestDox('getCountRecords() returns correct int when fetchField() returns numeric string')]
  public function getCountRecordsReturnsCorrectIntForNumericResult(): void {
    $this->connection->method('select')->willReturn($this->buildSelectStubWithFetchField('150'));

    $this->assertSame(150, $this->service->getCountRecords(Versions::Fifth));
  }

  #[Test]
  #[TestDox('updateDrawsCount() calls update with draws_count = existing + new_records')]
  public function updateDrawsCountCallsUpdateWithSummedCount(): void {
    $existing = 100;
    $new = 5;

    $select = $this->buildSelectStubWithFetchField((string) $existing);

    $update = $this->createMock(Update::class);
    $update->method('condition')->willReturnSelf();
    $update->expects($this->once())
      ->method('fields')
      ->with(['draws_count' => $existing + $new])
      ->willReturnSelf();
    $update->method('execute')->willReturn(NULL);

    $this->connection->method('select')->willReturn($select);
    $this->connection->method('update')->with(SchemaInterface::LOTTO_VERSIONS)->willReturn($update);

    $this->service->updateDrawsCount(Versions::Fifth, $new);
  }

  #[Test]
  #[TestDox('archivePrediction() returns early when record_id is null')]
  public function archivePredictionReturnsEarlyWhenRecordIdIsNull(): void {
    $this->moduleHandler->expects($this->never())->method('moduleExists');
    $this->connection->expects($this->never())->method('update');

    $this->service->archivePrediction(NULL);
  }

  #[Test]
  #[TestDox('archivePrediction() returns early when jpf_algo module is not enabled')]
  public function archivePredictionReturnsEarlyWhenModuleNotEnabled(): void {
    $this->moduleHandler->method('moduleExists')->with('jpf_algo')->willReturn(FALSE);
    $this->connection->expects($this->never())->method('update');

    $this->service->archivePrediction('42');
  }

  #[Test]
  #[TestDox('archivePrediction() calls update when record_id is set and module is enabled')]
  public function archivePredictionCallsUpdateWhenModuleIsEnabled(): void {
    $this->moduleHandler->method('moduleExists')->with('jpf_algo')->willReturn(TRUE);

    $update = $this->createMock(Update::class);
    $update->method('isNull')->willReturnSelf();
    $update->method('fields')->willReturnSelf();
    $update->method('execute')->willReturn(NULL);
    $this->connection->expects($this->once())->method('update')->willReturn($update);

    $this->service->archivePrediction('42');
  }

  #[Test]
  #[TestDox('importCsvFile() returns early without inserting when arrayFilter() returns no data')]
  public function importCsvFileReturnsEarlyWhenNoFilteredData(): void {
    $this->csvHelper->method('csvToArray')->willReturn([]);
    $this->csvHelper->method('arrayFilter')->willReturn([]);
    $this->connection->method('select')->willReturn($this->buildSelectStub());
    $this->connection->expects($this->never())->method('insert');

    $this->service->importCsvFile(Versions::Fifth);
  }

  #[Test]
  #[TestDox('importCsvFile() inserts records and calls updateDrawsCount and archivePrediction')]
  public function importCsvFileInsertsRecordsAndCallsUpdateAndArchive(): void {
    $record = ['version' => 'v5', 'year' => '2023', 'month' => '11', 'day' => '04'];

    $this->csvHelper->method('csvToArray')->willReturn([$record]);
    $this->csvHelper->method('arrayFilter')->willReturn([1699056000 => $record]);
    $this->schema->method('lottoDrawsFields')->willReturn(['id' => [], 'version' => [], 'year' => []]);
    $this->moduleHandler->method('moduleExists')->willReturn(FALSE);

    // select #1 → getLastRecord() (returns null via execute()->null-safe)
    // select #2 → getCountRecords() inside updateDrawsCount()
    $this->connection->method('select')->willReturnOnConsecutiveCalls(
      $this->buildSelectStub(),
      $this->buildSelectStubWithFetchField('10'),
    );

    $insert = $this->createMock(Insert::class);
    $insert->method('fields')->willReturnSelf();
    $insert->method('values')->willReturnSelf();
    $insert->method('execute')->willReturn(NULL);
    $this->connection->expects($this->once())->method('insert')
      ->with(SchemaInterface::LOTTO_DRAWS_TABLE)
      ->willReturn($insert);

    $update = $this->createMock(Update::class);
    $update->method('fields')->willReturnSelf();
    $update->method('condition')->willReturnSelf();
    $update->method('execute')->willReturn(NULL);
    $this->connection->expects($this->once())->method('update')
      ->with(SchemaInterface::LOTTO_VERSIONS)
      ->willReturn($update);

    $this->service->importCsvFile(Versions::Fifth);
  }

}
