<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_store\Enum\Versions;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Versions::class)]
#[Group('Custom')]
#[TestDox('Store: Versions enum unit')]
final class VersionsTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $date_formatter = $this->createMock(DateFormatterInterface::class);
    $date_formatter->method('format')->willReturn('6 novembre 2019');

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('date.formatter', $date_formatter);
    \Drupal::setContainer($container);
  }

  /**
   * @return array<string, array{Versions, string}>
   */
  public static function filenameProvider(): array {
    return [
      'First' => [Versions::First, 'loto'],
      'Second' => [Versions::Second, 'nouveau_loto'],
      'Third' => [Versions::Third, 'loto2017'],
      'Fourth' => [Versions::Fourth, 'loto_201902'],
      'Fifth' => [Versions::Fifth, 'loto_201911'],
    ];
  }

  /**
   * @return array<string, array{Versions, string}>
   */
  public static function letterIdentifierProvider(): array {
    return [
      'First → l' => [Versions::First, 'l'],
      'Second → m' => [Versions::Second, 'm'],
      'Third → n' => [Versions::Third, 'n'],
      'Fourth → o' => [Versions::Fourth, 'o'],
      'Fifth → p' => [Versions::Fifth, 'p'],
    ];
  }

  #[Test]
  #[TestDox('currentVersion() returns a non-null version')]
  public function currentVersionReturnsNonNull(): void {
    $this->assertNotNull(Versions::currentVersion());
  }

  #[Test]
  #[TestDox('currentVersion() returns Fifth in 2026')]
  public function currentVersionReturnsFifthIn2026(): void {
    $this->assertSame(Versions::Fifth, Versions::currentVersion());
  }

  #[Test]
  #[TestDox('filename() returns the correct filename for each version')]
  #[DataProvider('filenameProvider')]
  public function filenameReturnsCorrectFilename(Versions $version, string $expected): void {
    $this->assertSame($expected, $version->filename());
  }

  #[Test]
  #[TestDox('begin() and end() dates are consistent (begin < end for closed versions)')]
  public function beginAndEndDatesAreConsistent(): void {
    foreach (Versions::cases() as $version) {
      if ($version->end() === '') {
        continue;
      }

      $this->assertLessThan($version->end(), $version->begin(), "$version->name begin < end.");
    }
  }

  #[Test]
  #[TestDox('begin() returns correct date for First version')]
  public function beginReturnsCorrectDateForFirst(): void {
    $this->assertSame('19760519', Versions::First->begin());
  }

  #[Test]
  #[TestDox('end() returns empty string for Fifth (current version)')]
  public function endReturnsEmptyStringForFifth(): void {
    $this->assertSame('', Versions::Fifth->end());
  }

  #[Test]
  #[TestDox('dateFormat() returns Ymd for First, d/m/Y for others')]
  public function dateFormatReturnsCorrectFormat(): void {
    $this->assertSame('Ymd', Versions::First->dateFormat());

    foreach ([Versions::Second, Versions::Third, Versions::Fourth, Versions::Fifth] as $version) {
      $this->assertSame('d/m/Y', $version->dateFormat(), "$version->name dateFormat.");
    }
  }

  #[Test]
  #[TestDox('dayMethod() returns uppercaseDayCode for First, uppercaseFrenchLabel for others')]
  public function dayMethodReturnsCorrectMethod(): void {
    $this->assertSame('uppercaseDayCode', Versions::First->dayMethod());

    foreach ([Versions::Second, Versions::Third, Versions::Fourth, Versions::Fifth] as $version) {
      $this->assertSame('uppercaseFrenchLabel', $version->dayMethod(), "$version->name dayMethod.");
    }
  }

  #[Test]
  #[TestDox('letterIdentifier() returns the correct letter for each version')]
  #[DataProvider('letterIdentifierProvider')]
  public function letterIdentifierReturnsCorrectLetter(Versions $version, string $expected): void {
    $this->assertSame($expected, $version->letterIdentifier());
  }

  #[Test]
  #[TestDox('drawnBalls() returns 6 for First, 5 for others')]
  public function drawnBallsReturnsCorrectCount(): void {
    $this->assertSame(6, Versions::First->drawnBalls());

    foreach ([Versions::Second, Versions::Third, Versions::Fourth, Versions::Fifth] as $version) {
      $this->assertSame(5, $version->drawnBalls(), "$version->name drawnBalls.");
    }
  }

  #[Test]
  #[TestDox('FILE_EXTENSION constant equals .csv')]
  public function fileExtensionConstantEqualsCsv(): void {
    $this->assertSame('.csv', Versions::FILE_EXTENSION);
  }

  #[Test]
  #[TestDox('versionPath() returns a path containing DRUPAL_ROOT and the version value')]
  public function versionPathContainsDrupalRootAndVersionValue(): void {
    $path = Versions::Fifth->versionPath();

    $this->assertStringContainsString(DRUPAL_ROOT, $path);
    $this->assertStringContainsString('v5', $path);
    $this->assertStringEndsWith('/', $path);
  }

  #[Test]
  #[TestDox('filePath() returns versionPath concatenated with filename and .csv extension')]
  public function filePathEndsWithFilenameAndCsvExtension(): void {
    $path = Versions::Fifth->filePath();

    $this->assertStringEndsWith('loto_201911.csv', $path);
    $this->assertStringContainsString(DRUPAL_ROOT, $path);
  }

  #[Test]
  #[TestDox('archivePath() returns versionPath concatenated with filename and .zip extension')]
  public function archivePathEndsWithFilenameAndZipExtension(): void {
    $path = Versions::Fifth->archivePath();

    $this->assertStringEndsWith('loto_201911.zip', $path);
    $this->assertStringContainsString(DRUPAL_ROOT, $path);
  }

  #[Test]
  #[TestDox('humanReadableBeginDate() returns the formatted begin date via date.formatter service')]
  public function humanReadableBeginDateReturnsFormattedDate(): void {
    $this->assertSame('6 novembre 2019', Versions::Fifth->humanReadableBeginDate());
  }

}
