<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_sender\Build;

use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_sender module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Sender: Build configuration')]
final class JpfSenderBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_sender.info.yml');

    $this->assertSame('JPF : Sender', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_algo:jpf_algo', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_sender.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_sender.skip_procedural_hook_scan'] ?? NULL);
  }

}
