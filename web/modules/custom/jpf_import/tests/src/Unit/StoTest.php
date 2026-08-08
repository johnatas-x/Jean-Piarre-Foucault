<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_import\Unit;

use Drupal\jpf_import\Api\Sto;
use Drupal\jpf_store\Enum\Versions;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Sto::class)]
#[Group('Custom')]
#[TestDox('Import: Sto unit')]
final class StoTest extends UnitTestCase {

  #[Test]
  #[TestDox('buildDownloadUrl() returns a URL starting with the FDJ API base')]
  public function buildDownloadUrlReturnsUrlWithFdjApiBase(): void {
    $url = Sto::buildDownloadUrl(Versions::Fifth);

    $this->assertStringStartsWith('https://www.sto.api.fdj.fr/', $url);
  }

  #[Test]
  #[TestDox('buildDownloadUrl() includes the version letter identifier in the token')]
  public function buildDownloadUrlIncludesVersionLetterInToken(): void {
    $url = Sto::buildDownloadUrl(Versions::Fifth);

    $this->assertStringContainsString(Versions::Fifth->letterIdentifier(), $url);
  }

  #[Test]
  #[TestDox('buildDownloadUrl() produces different URLs for different versions')]
  public function buildDownloadUrlProducesDifferentUrlsPerVersion(): void {
    $url_first = Sto::buildDownloadUrl(Versions::First);
    $url_fifth = Sto::buildDownloadUrl(Versions::Fifth);

    $this->assertNotSame($url_first, $url_fifth);
  }

  #[Test]
  #[TestDox('buildDownloadUrl() builds the exact expected URL for Versions::Fifth')]
  public function buildDownloadUrlBuildsExactUrlForVersionsFifth(): void {
    $expected = 'https://www.sto.api.fdj.fr/anonymous/service-draw-info/v3/documentations/1a2b3c4d-9876-4562-b3fc-2c963f66afp6';

    $this->assertSame($expected, Sto::buildDownloadUrl(Versions::Fifth));
  }

  #[Test]
  #[TestDox('buildDownloadUrl() builds the exact expected URL for Versions::First')]
  public function buildDownloadUrlBuildsExactUrlForVersionsFirst(): void {
    $expected = 'https://www.sto.api.fdj.fr/anonymous/service-draw-info/v3/documentations/1a2b3c4d-9876-4562-b3fc-2c963f66afl6';

    $this->assertSame($expected, Sto::buildDownloadUrl(Versions::First));
  }

}
