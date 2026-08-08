<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_import\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_import module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Import: Build configuration')]
final class JpfImportBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_import.info.yml');

    $this->assertSame('JPF : Data import', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_store:jpf_store', $info['dependencies'] ?? []);
    $this->assertContains('jpf_utils:jpf_utils', $info['dependencies'] ?? []);
    $this->assertContains('ultimate_cron:ultimate_cron', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_import.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_import.skip_procedural_hook_scan'] ?? NULL);
  }

}
