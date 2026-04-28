<?php

declare(strict_types=1);

namespace Scripts;

use Composer\Installer\PackageEvent;

/**
 * Handles Composer event hooks.
 */
class ComposerScripts {

  /**
   * Directories to remove via vendor hardening.
   */
  private const array HARDENING_DIRS = [
    'examples',
    'test',
    'tests',
    '.github',
    '.gitlab',
    '.circleci',
    '.tugboat',
    '.ddev',
    'doc',
    'docs',
    'Docs',
  ];

  /**
   * Triggers the translation script for Drupal packages after installation.
   *
   * @throws \JsonException
   */
  public static function postPackageInstall(PackageEvent $event): void {
    $command = $_SERVER['argv'][1] ?? '';

    if ($command !== 'require') {
      return;
    }

    $package = $event->getOperation()->getPackage();
    $name = $package->getName();

    self::updateVendorHardening($event, $name);
  }

  /**
   * Adds missing vendor hardening entries for the installed package.
   *
   * @throws \JsonException
   */
  private static function updateVendorHardening(PackageEvent $event, string $packageName): void {
    $packagePath = self::resolvePackagePath($event, $packageName);

    if ($packagePath === NULL || !is_dir($packagePath)) {
      return;
    }

    $dirsToHarden = self::findHardeningDirs($packagePath);

    if ($dirsToHarden === []) {
      return;
    }

    $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
    $composerJsonPath = dirname($vendorDir) . '/composer.json';
    $composerJson = json_decode(file_get_contents($composerJsonPath), TRUE, 512, JSON_THROW_ON_ERROR);
    $hardening = $composerJson['extra']['drupal-core-vendor-hardening'] ?? [];

    $existingDirs = $hardening[$packageName] ?? [];
    $newDirs = array_values(array_unique([...$existingDirs, ...$dirsToHarden]));
    sort($newDirs);

    if ($newDirs === $existingDirs) {
      return;
    }

    $hardening[$packageName] = $newDirs;
    ksort($hardening);
    $composerJson['extra']['drupal-core-vendor-hardening'] = $hardening;

    file_put_contents(
      $composerJsonPath,
      json_encode($composerJson, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
    );

    $event->getIO()->write("<info>Updated vendor hardening for $packageName</info>");
  }

  /**
   * Resolves the install path for a package.
   */
  private static function resolvePackagePath(PackageEvent $event, string $packageName): ?string {
    $package = $event->getOperation()->getPackage();
    $type = $package->getType();
    $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
    $projectRoot = dirname($vendorDir);

    $shortName = str_contains($packageName, '/') ? explode('/', $packageName)[1] : $packageName;

    return match ($type) {
      'drupal-module' => $projectRoot . '/web/modules/contrib/' . $shortName,
      'drupal-theme' => $projectRoot . '/web/themes/contrib/' . $shortName,
      'drupal-library' => $projectRoot . '/web/libraries/' . $shortName,
      default => $vendorDir . '/' . $packageName,
    };
  }

  /**
   * Finds directories that should be hardened in a package.
   *
   * @return array<string>
   */
  private static function findHardeningDirs(string $packagePath): array {
    $found = [];

    foreach (self::HARDENING_DIRS as $dir) {
      if (is_dir($packagePath . '/' . $dir)) {
        $found[] = $dir;
      }
    }

    return $found;
  }

}
