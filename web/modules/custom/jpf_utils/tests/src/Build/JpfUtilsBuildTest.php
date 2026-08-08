<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_utils\Build;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_utils module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Utils: Build configuration')]
final class JpfUtilsBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_utils.info.yml');

    $this->assertSame('JPF : Utils', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11.1', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertTrue($info['required'] ?? FALSE);
  }

  #[Test]
  #[TestDox('Services definition has skip_procedural_hook_scan parameter')]
  public function servicesDefinitionHasSkipParameter(): void {
    $services = $this->parseYaml('jpf_utils.services.yml');

    $this->assertTrue($services['parameters']['jpf_utils.skip_procedural_hook_scan'] ?? NULL);
  }

}
