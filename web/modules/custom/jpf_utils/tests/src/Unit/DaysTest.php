<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_utils\Unit;

use Drupal\jpf_utils\Enum\Days;
use Drupal\jpf_utils\Traits\EnumToArrayTrait;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Days::class)]
#[CoversClass(EnumToArrayTrait::class)]
#[Group('Custom')]
#[TestDox('Utils: Days enum unit')]
final class DaysTest extends UnitTestCase {

  /**
   * @return array<string, array{string, Days|null}>
   */
  public static function fromMethodUppercaseDayCodeProvider(): array {
    return [
      'LU → Monday' => ['LU', Days::Monday],
      'MA → Tuesday' => ['MA', Days::Tuesday],
      'ME → Wednesday' => ['ME', Days::Wednesday],
      'JE → Thursday' => ['JE', Days::Thursday],
      'VE → Friday' => ['VE', Days::Friday],
      'SA → Saturday' => ['SA', Days::Saturday],
      'DI → Sunday' => ['DI', Days::Sunday],
    ];
  }

  /**
   * @return array<string, array{string, Days|null}>
   */
  public static function fromMethodUppercaseFrenchLabelProvider(): array {
    return [
      'LUNDI → Monday' => ['LUNDI', Days::Monday],
      'MARDI → Tuesday' => ['MARDI', Days::Tuesday],
      'MERCREDI → Wednesday' => ['MERCREDI', Days::Wednesday],
      'JEUDI → Thursday' => ['JEUDI', Days::Thursday],
      'VENDREDI → Friday' => ['VENDREDI', Days::Friday],
      'SAMEDI → Saturday' => ['SAMEDI', Days::Saturday],
      'DIMANCHE → Sunday' => ['DIMANCHE', Days::Sunday],
    ];
  }

  #[Test]
  #[TestDox('fromMethod() with uppercaseDayCode returns the correct case')]
  #[DataProvider('fromMethodUppercaseDayCodeProvider')]
  public function fromMethodWithDayCodeReturnsCorrectCase(string $value, Days $expected): void {
    $this->assertSame($expected, Days::fromMethod('uppercaseDayCode', $value));
  }

  #[Test]
  #[TestDox('fromMethod() with uppercaseFrenchLabel returns the correct case')]
  #[DataProvider('fromMethodUppercaseFrenchLabelProvider')]
  public function fromMethodWithFrenchLabelReturnsCorrectCase(string $value, Days $expected): void {
    $this->assertSame($expected, Days::fromMethod('uppercaseFrenchLabel', $value));
  }

  #[Test]
  #[TestDox('fromMethod() returns null for unknown method')]
  public function fromMethodReturnsNullForUnknownMethod(): void {
    $this->assertNull(Days::fromMethod('nonExistentMethod', 'LUNDI'));
  }

  #[Test]
  #[TestDox('fromMethod() returns null for null value')]
  public function fromMethodReturnsNullForNullValue(): void {
    $this->assertNull(Days::fromMethod('uppercaseFrenchLabel', NULL));
  }

  #[Test]
  #[TestDox('fromMethod() returns null when value does not match any case')]
  public function fromMethodReturnsNullForUnknownValue(): void {
    $this->assertNull(Days::fromMethod('uppercaseFrenchLabel', 'UNKNOWN'));
  }

  #[Test]
  #[TestDox('uppercaseDayCode() returns 2-letter uppercase code')]
  public function uppercaseDayCodeReturnsTwoLetterCode(): void {
    $this->assertSame('LU', Days::Monday->uppercaseDayCode());
    $this->assertSame('SA', Days::Saturday->uppercaseDayCode());
    $this->assertSame('DI', Days::Sunday->uppercaseDayCode());
  }

  #[Test]
  #[TestDox('uppercaseFrenchLabel() returns uppercase French day name')]
  public function uppercaseFrenchLabelReturnsUppercaseName(): void {
    $this->assertSame('LUNDI', Days::Monday->uppercaseFrenchLabel());
    $this->assertSame('SAMEDI', Days::Saturday->uppercaseFrenchLabel());
    $this->assertSame('DIMANCHE', Days::Sunday->uppercaseFrenchLabel());
  }

  #[Test]
  #[TestDox('capitalizeFrenchLabel() returns capitalized French day name')]
  public function capitalizeFrenchLabelReturnsCapitalizedName(): void {
    $this->assertSame('Lundi', Days::Monday->capitalizeFrenchLabel());
    $this->assertSame('Samedi', Days::Saturday->capitalizeFrenchLabel());
    $this->assertSame('Dimanche', Days::Sunday->capitalizeFrenchLabel());
  }

  #[Test]
  #[TestDox('names() returns all 7 day names')]
  public function namesReturnsAllDayNames(): void {
    $names = Days::names();

    $this->assertCount(7, $names);
    $this->assertContains('Monday', $names);
    $this->assertContains('Sunday', $names);
  }

  #[Test]
  #[TestDox('values() returns all 7 day values')]
  public function valuesReturnsAllDayValues(): void {
    $values = Days::values();

    $this->assertCount(7, $values);
    $this->assertContains('Monday', $values);
    $this->assertContains('Sunday', $values);
  }

  #[Test]
  #[TestDox('array() returns a name => value map for all days')]
  public function arrayReturnsNameValueMap(): void {
    $map = Days::array();

    $this->assertCount(7, $map);
    $this->assertSame('Monday', $map['Monday']);
    $this->assertSame('Sunday', $map['Sunday']);
  }

}
