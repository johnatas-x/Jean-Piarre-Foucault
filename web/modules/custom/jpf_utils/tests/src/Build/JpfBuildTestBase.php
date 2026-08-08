<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_utils\Build;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for all JPF module Build tests.
 */
#[CoversNothing]
abstract class JpfBuildTestBase extends TestCase {

  /**
   * The extension path.
   */
  protected string $extensionPath;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $reflection = new \ReflectionClass($this);
    $this->extensionPath = dirname((string) $reflection->getFileName(), 4);
  }

  /**
   * Returns the absolute path to a file from the extension.
   *
   * @param string $relative
   *   The relative path to the file.
   *
   * @return string
   *   The absolute path.
   */
  protected function getExtensionFile(string $relative): string {
    return $this->extensionPath . DIRECTORY_SEPARATOR . $relative;
  }

  /**
   * Loads a YAML file and returns it as an array.
   *
   * @param string $relative
   *   The relative path to the YAML file.
   *
   * @return array<mixed>
   *   The parsed content.
   */
  protected function parseYaml(string $relative): array {
    $file = $this->getExtensionFile($relative);
    $this->assertFileExists($file, sprintf('The file %s exists.', $relative));
    $parsed = Yaml::parseFile($file);
    $this->assertIsArray($parsed, sprintf('The YAML file %s is correctly parsed into an array.', $relative));
    return $parsed ?? [];
  }

}
