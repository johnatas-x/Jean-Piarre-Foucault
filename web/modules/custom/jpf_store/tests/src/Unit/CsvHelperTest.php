<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\jpf_store\Enum\Versions;
use Drupal\jpf_store\Services\CsvHelper;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(CsvHelper::class)]
#[Group('Custom')]
#[TestDox('Store: CsvHelper service unit')]
final class CsvHelperTest extends UnitTestCase {

  private CsvHelper $service;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->service = new CsvHelper();
  }

  /**
   * Writes content to a temp file and returns its path.
   */
  private function writeTempCsv(string $content): string {
    $path = tempnam(sys_get_temp_dir(), 'jpf_csv_test_');
    file_put_contents($path, $content);
    return $path;
  }

  #[Test]
  #[TestDox('csvToArray() returns empty array when file does not exist')]
  public function csvToArrayReturnsEmptyArrayWhenFileNotFound(): void {
    set_error_handler(static fn() => TRUE);
    $result = $this->service->csvToArray('/nonexistent/path/file.csv');
    restore_error_handler();

    $this->assertSame([], $result);
  }

  #[Test]
  #[TestDox('csvToArray() throws RuntimeException when file has fewer than 2 rows')]
  public function csvToArrayThrowsWhenFileTooShort(): void {
    $path = $this->writeTempCsv("date_de_tirage;boule_1\n");

    $this->expectException(\RuntimeException::class);
    $this->service->csvToArray($path);

    unlink($path);
  }

  #[Test]
  #[TestDox('csvToArray() returns combined header+row array for valid CSV')]
  public function csvToArrayReturnsCombinedArrayForValidCsv(): void {
    $path = $this->writeTempCsv("date_de_tirage;boule_1\n04/11/2023;7\n");

    $result = $this->service->csvToArray($path);
    unlink($path);

    $this->assertCount(1, $result);
    $this->assertSame('04/11/2023', $result[0]['date_de_tirage']);
    $this->assertSame('7', $result[0]['boule_1']);
  }

  #[Test]
  #[TestDox('arrayFilter() returns empty array when csv_data is empty')]
  public function arrayFilterReturnsEmptyArrayWhenCsvDataIsEmpty(): void {
    $result = $this->service->arrayFilter([], Versions::Fifth, NULL);

    $this->assertSame([], $result);
  }

  #[Test]
  #[TestDox('arrayFilter() skips rows where date_de_tirage is not a string')]
  public function arrayFilterSkipsRowsWithNonStringDate(): void {
    $row = ['date_de_tirage' => NULL, 'boule_1' => '7'];

    $result = $this->service->arrayFilter([$row], Versions::Fifth, NULL);

    $this->assertSame([], $result);
  }

  #[Test]
  #[TestDox('arrayFilter() maps a valid row to a record indexed by timestamp')]
  public function arrayFilterMapsValidRowToRecord(): void {
    $row = [
      'date_de_tirage' => '04/11/2023',
      'jour_de_tirage' => 'SAMEDI',
      '1er_ou_2eme_tirage' => '1',
      'boule_1' => '7',
      'boule_2' => '12',
      'boule_3' => '23',
      'boule_4' => '34',
      'boule_5' => '45',
      'boule_6' => '',
      'boule_complementaire' => '3',
      'numero_chance' => '5',
    ];

    $result = $this->service->arrayFilter([$row], Versions::Fifth, NULL);

    $this->assertCount(1, $result);
    $record = reset($result);
    $this->assertSame('v5', $record['version']);
    $this->assertSame(2023, $record['year']);
    $this->assertSame(11, $record['month']);
    $this->assertSame(4, $record['day']);
    $this->assertSame(7, $record['ball_1']);
  }

  #[Test]
  #[TestDox('arrayFilter() stops when a row matches last_record')]
  public function arrayFilterStopsWhenRowMatchesLastRecord(): void {
    $last_record = [
      'version' => 'v5',
      'year' => '2023',
      'month' => '11',
      'day' => '4',
      'which_draw' => '1',
      'day_of_week' => 'Samedi',
      'ball_1' => '7',
      'ball_2' => '12',
      'ball_3' => '23',
      'ball_4' => '34',
      'ball_5' => '45',
      'ball_6' => NULL,
      'ball_0' => '3',
    ];

    $row1 = [
      'date_de_tirage' => '04/11/2023',
      'jour_de_tirage' => 'SAMEDI',
      '1er_ou_2eme_tirage' => '1',
      'boule_1' => '7',
      'boule_2' => '12',
      'boule_3' => '23',
      'boule_4' => '34',
      'boule_5' => '45',
      'boule_6' => '',
      'boule_complementaire' => '3',
      'numero_chance' => '',
    ];
    $row2 = [
      'date_de_tirage' => '05/11/2023',
      'jour_de_tirage' => 'DIMANCHE',
      '1er_ou_2eme_tirage' => '1',
      'boule_1' => '1',
      'boule_2' => '2',
      'boule_3' => '3',
      'boule_4' => '4',
      'boule_5' => '5',
      'boule_6' => '',
      'boule_complementaire' => '6',
      'numero_chance' => '',
    ];

    $result = $this->service->arrayFilter([$row1, $row2], Versions::Fifth, $last_record);

    $this->assertCount(0, $result);
  }

}
