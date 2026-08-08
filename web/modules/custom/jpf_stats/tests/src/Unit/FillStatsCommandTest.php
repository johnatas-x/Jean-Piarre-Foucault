<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_stats\Unit;

use Drupal\jpf_stats\Drush\Commands\FillStatsCommand;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\LoggerInterface;

#[CoversClass(FillStatsCommand::class)]
#[Group('Custom')]
#[TestDox('Stats: FillStatsCommand unit')]
final class FillStatsCommandTest extends UnitTestCase {

  private FillStatsCommand $command;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->command = new FillStatsCommand($this->createMock(LoggerInterface::class));
  }

  #[Test]
  #[TestDox('NAME constant equals fill-lotto-stats')]
  public function nameConstantEqualsFillLottoStats(): void {
    $this->assertSame('fill-lotto-stats', FillStatsCommand::NAME);
  }

}
