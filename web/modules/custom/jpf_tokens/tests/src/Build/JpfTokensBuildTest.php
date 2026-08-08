<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_tokens\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_tokens module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Tokens: Build configuration')]
final class JpfTokensBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_tokens.info.yml');

    $this->assertSame('JPF : Tokens', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11.1', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('token:token', $info['dependencies'] ?? []);
    $this->assertContains('jpf_store:jpf_store', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition has skip_procedural_hook_scan parameter')]
  public function servicesDefinitionHasSkipParameter(): void {
    $services = $this->parseYaml('jpf_tokens.services.yml');

    $this->assertTrue($services['parameters']['jpf_tokens.skip_procedural_hook_scan'] ?? NULL);
  }

}
