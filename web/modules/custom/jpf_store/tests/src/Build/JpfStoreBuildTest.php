<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_store\Build;

use Drupal\jpf_store\Services\CsvHelper;
use Drupal\jpf_store\Services\Database;
use Drupal\jpf_store\Services\Schema;
use Drupal\Tests\jpf_utils\Build\JpfBuildTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Build tests for jpf_store module YAML configuration.
 */
#[Group('Custom')]
#[TestDox('Store: Build configuration')]
final class JpfStoreBuildTest extends JpfBuildTestBase {

  #[Test]
  #[TestDox('Info definition is correct')]
  public function infoDefinition(): void {
    $info = $this->parseYaml('jpf_store.info.yml');

    $this->assertSame('JPF : Data store', $info['name'] ?? NULL);
    $this->assertSame('module', $info['type'] ?? NULL);
    $this->assertSame('^11', $info['core_version_requirement'] ?? NULL);
    $this->assertSame('Custom', $info['package'] ?? NULL);
    $this->assertContains('jpf_utils:jpf_utils', $info['dependencies'] ?? []);
  }

  #[Test]
  #[TestDox('Services definition is correct')]
  public function servicesDefinition(): void {
    $services = $this->parseYaml('jpf_store.services.yml');

    $this->assertArrayHasKey('parameters', $services);
    $this->assertTrue($services['parameters']['jpf_store.skip_procedural_hook_scan'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autoconfigure'] ?? NULL);
    $this->assertTrue($services['services']['_defaults']['autowire'] ?? NULL);
    $this->assertArrayHasKey(CsvHelper::class, $services['services']);
    $this->assertArrayHasKey(Database::class, $services['services']);
    $this->assertArrayHasKey(Schema::class, $services['services']);
  }

}
