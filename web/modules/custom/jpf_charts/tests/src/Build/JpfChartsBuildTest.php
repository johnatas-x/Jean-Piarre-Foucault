<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_charts\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_charts module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Charts: Build configuration')]
final class JpfChartsBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_charts.info.yml');

    $this->assertSame('JPF : Data charts', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('drupal:charts', $info['dependencies'] ?? []);
    $this->assertContains('drupal:charts_chartjs', $info['dependencies'] ?? []);
    $this->assertContains('jpf_stats:jpf_stats', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_charts.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_charts.skip_procedural_hook_scan'] ?? NULL);
  }

}
