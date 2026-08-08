<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_views\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_views module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Views: Build configuration')]
final class JpfViewsBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_views.info.yml');

    $this->assertSame('JPF : Data views', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11.1', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('drupal:view_custom_table', $info['dependencies'] ?? []);
    $this->assertContains('drupal:views', $info['dependencies'] ?? []);
    $this->assertContains('jpf_stats:jpf_stats', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition has skip_procedural_hook_scan parameter')]
  public function servicesDefinitionHasSkipParameter(): void {
    $services = $this->parseYaml('jpf_views.services.yml');

    $this->assertTrue($services['parameters']['jpf_views.skip_procedural_hook_scan'] ?? NULL);
  }

}
