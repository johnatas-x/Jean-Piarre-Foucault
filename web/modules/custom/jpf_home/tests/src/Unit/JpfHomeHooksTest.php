<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_home\Unit;

use Drupal\jpf_home\Hook\JpfHomeHooks;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(JpfHomeHooks::class)]
#[Group('Custom')]
#[TestDox('Home: JpfHomeHooks unit')]
final class JpfHomeHooksTest extends UnitTestCase {

  private JpfHomeHooks $hooks;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->hooks = new JpfHomeHooks();
  }

  #[Test]
  #[TestDox('theme() defines homepage theme')]
  public function themeDefinesHomepageTheme(): void {
    $result = $this->hooks->theme();

    $this->assertArrayHasKey('homepage', $result);
  }

  #[Test]
  #[TestDox('theme() homepage uses homepage template')]
  public function themeHomepageUsesHomepageTemplate(): void {
    $result = $this->hooks->theme();

    $this->assertSame('homepage', $result['homepage']['template']);
  }

  #[Test]
  #[TestDox('theme() homepage variables include title, last_draw, last_predict and next_predict')]
  public function themeHomepageVariablesAreComplete(): void {
    $variables = $this->hooks->theme()['homepage']['variables'];

    $this->assertArrayHasKey('title', $variables);
    $this->assertArrayHasKey('last_draw', $variables);
    $this->assertArrayHasKey('last_predict', $variables);
    $this->assertArrayHasKey('next_predict', $variables);
  }

  #[Test]
  #[TestDox('theme() homepage ball variables default to empty balls and null lucky')]
  public function themeHomepageBallVariablesDefaultToEmptyBallsAndNullLucky(): void {
    $variables = $this->hooks->theme()['homepage']['variables'];

    foreach (['last_draw', 'last_predict', 'next_predict'] as $key) {
      $this->assertSame([], $variables[$key]['balls'], sprintf('%s.balls defaults to [].', $key));
      $this->assertNull($variables[$key]['lucky'], sprintf('%s.lucky defaults to NULL.', $key));
    }
  }

}
