<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Unit;

use Drupal\jpf_store\Enum\Balls;
use Drupal\jpf_store\Services\Schema;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Schema::class)]
#[Group('Custom')]
#[TestDox('Store: Schema service unit')]
final class SchemaTest extends UnitTestCase {

  private Schema $schema;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->schema = new Schema();
  }

  #[Test]
  #[TestDox('lottoDrawsFields() contains id, version, year, month, day, which_draw, day_of_week')]
  public function lottoDrawsFieldsContainsBaseFields(): void {
    $fields = $this->schema->lottoDrawsFields();

    $this->assertArrayHasKey('id', $fields);
    $this->assertArrayHasKey('version', $fields);
    $this->assertArrayHasKey('year', $fields);
    $this->assertArrayHasKey('month', $fields);
    $this->assertArrayHasKey('day', $fields);
    $this->assertArrayHasKey('which_draw', $fields);
    $this->assertArrayHasKey('day_of_week', $fields);
  }

  #[Test]
  #[TestDox('lottoDrawsFields() contains a column for every Balls case')]
  public function lottoDrawsFieldsContainsAllBallColumns(): void {
    $fields = $this->schema->lottoDrawsFields();

    foreach (Balls::cases() as $ball) {
      $this->assertArrayHasKey($ball->columnName(), $fields, "Field {$ball->columnName()} exists.");
    }
  }

  #[Test]
  #[TestDox('lottoStatsFields() contains ball, count, percentage, last, best_day, frequency')]
  public function lottoStatsFieldsContainsExpectedKeys(): void {
    $fields = $this->schema->lottoStatsFields();

    $this->assertArrayHasKey('ball', $fields);
    $this->assertArrayHasKey('count', $fields);
    $this->assertArrayHasKey('percentage', $fields);
    $this->assertArrayHasKey('last', $fields);
    $this->assertArrayHasKey('best_day', $fields);
    $this->assertArrayHasKey('frequency', $fields);
  }

  #[Test]
  #[TestDox('versionStatsFields() contains version and draws_count')]
  public function versionStatsFieldsContainsExpectedKeys(): void {
    $fields = $this->schema->versionStatsFields();

    $this->assertArrayHasKey('version', $fields);
    $this->assertArrayHasKey('draws_count', $fields);
  }

}
