<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_home\Build;

use Drupal\jpf_home\Services\HomepageHelper;
use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_home module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Home: Build configuration')]
final class JpfHomeBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_home.info.yml');

    $this->assertSame('JPF : Home', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11.4', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_store:jpf_store', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_home.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_home.skip_procedural_hook_scan'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autoconfigure'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autowire'] ?? NULL);
    $this->assertArrayHasKey(HomepageHelper::class, $services['services']);
  }

  #[Test]
  #[TestDox('Libraries definition is correct')]
  public function librariesDefinition(): void {
    $libraries = $this->parseYaml('jpf_home.libraries.yml');

    $this->assertArrayHasKey('jpf_home', $libraries);
    $this->assertSame('1.x', $libraries['jpf_home']['version'] ?? NULL);
    $this->assertArrayHasKey('theme', $libraries['jpf_home']['css'] ?? []);
    $this->assertArrayHasKey('assets/css/jpf_home.css', $libraries['jpf_home']['css']['theme'] ?? []);
    $this->assertFileExists($this->getExtensionFile('assets/css/jpf_home.css'));
  }

}
