<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_stats\Build;

use Drupal\jpf_stats\Services\FillStats;
use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_stats module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Stats: Build configuration')]
final class JpfStatsBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_stats.info.yml');

    $this->assertSame('JPF : Data stats', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_import:jpf_import', $info['dependencies'] ?? []);
    $this->assertContains('jpf_store:jpf_store', $info['dependencies'] ?? []);
    $this->assertContains('jpf_utils:jpf_utils', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_stats.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_stats.skip_procedural_hook_scan'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autoconfigure'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autowire'] ?? NULL);
    $this->assertArrayHasKey(FillStats::class, $services['services']);
  }

}
