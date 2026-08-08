<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_algo\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_algo module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Algo: Build configuration')]
final class JpfAlgoBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_algo.info.yml');

    $this->assertSame('JPF : Algorithm', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_stats:jpf_stats', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_algo.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_algo.skip_procedural_hook_scan'] ?? NULL);
  }

}
