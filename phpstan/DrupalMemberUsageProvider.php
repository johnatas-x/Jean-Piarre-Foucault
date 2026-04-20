<?php

declare(strict_types=1);

namespace App\PHPStan;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

/**
 * Marks Drupal magic members as used for dead code detection.
 *
 * Methods: public methods in magic namespaces (Access, Controller, Plugin)
 * or specific magic classes are marked as used.
 *
 * Properties:
 * - Entity namespace: id/label properties (read by Drupal config system).
 * - Plugin namespace: protected overrides of parent properties.
 */
final class DrupalMemberUsageProvider extends ReflectionBasedMemberUsageProvider {

  /**
   * Namespace patterns marking class methods as used.
   *
   * @var list<string>
   */
  private const array NAMESPACE_PATTERNS = [
    '\\Access\\',
    '\\Commands\\',
    '\\Controller\\',
    '\\Cron\\',
    '\\Plugin\\',
  ];

  /**
   * Marks public methods from Drupal magic classes/namespaces as used.
   *
   * @param \ReflectionMethod $method
   *   The method to evaluate.
   *
   * @return \ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData|null
   *   Usage data if marked as used, null otherwise.
   */
  public function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData {
    $class = $method->getDeclaringClass();
    $className = $class->getName();

    if (!$method->isPublic()) {
      return NULL;
    }

    foreach (self::NAMESPACE_PATTERNS as $pattern) {
      if (str_contains($className, $pattern)) {
        return VirtualUsageData::withNote('Drupal magic namespace: ' . $pattern);
      }
    }

    return NULL;
  }

  /**
   * Marks Drupal magic properties as read.
   *
   * @param \ReflectionProperty $property
   *   The property to evaluate.
   *
   * @return \ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData|null
   *   Usage data if marked as read, null otherwise.
   */
  protected function shouldMarkPropertyAsRead(ReflectionProperty $property): ?VirtualUsageData {
    return $this->shouldMarkEntityProperty($property) ?? $this->shouldMarkPluginPropertyOverride($property);
  }

  /**
   * Marks Drupal magic properties as written.
   *
   * @param \ReflectionProperty $property
   *   The property to evaluate.
   *
   * @return \ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData|null
   *   Usage data if marked as written, null otherwise.
   */
  protected function shouldMarkPropertyAsWritten(ReflectionProperty $property): ?VirtualUsageData {
    return $this->shouldMarkEntityProperty($property) ?? $this->shouldMarkPluginPropertyOverride($property);
  }

  /**
   * Checks if property is Entity magic property (id/label).
   *
   * @param \ReflectionProperty $property
   *   The property to evaluate.
   *
   * @return \ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData|null
   *   Usage data if magic property, null otherwise.
   */
  private function shouldMarkEntityProperty(ReflectionProperty $property): ?VirtualUsageData {
    $class = $property->getDeclaringClass();
    $className = $class->getName();
    $propertyName = $property->getName();

    if (!str_contains($className, '\\Entity\\')) {
      return NULL;
    }

    if (in_array($propertyName, ['id', 'label'], TRUE)) {
      return VirtualUsageData::withNote('Drupal magic property');
    }

    return NULL;
  }

  /**
   * Checks if property is a Plugin property override.
   *
   * @param \ReflectionProperty $property
   *   The property to evaluate.
   *
   * @return \ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData|null
   *   Usage data if plugin override, null otherwise.
   */
  private function shouldMarkPluginPropertyOverride(ReflectionProperty $property): ?VirtualUsageData {
    $class = $property->getDeclaringClass();

    if (!str_contains($class->getName(), '\\Plugin\\')) {
      return NULL;
    }

    if (!$property->isProtected()) {
      return NULL;
    }

    $parent = $class->getParentClass();

    if ($parent instanceof ReflectionClass && $parent->hasProperty($property->getName())) {
      return VirtualUsageData::withNote('Drupal plugin property override');
    }

    return NULL;
  }

}
