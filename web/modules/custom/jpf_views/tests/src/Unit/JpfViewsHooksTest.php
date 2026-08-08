<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_views\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\jpf_store\Services\SchemaInterface;
use Drupal\jpf_views\Hook\JpfViewsHooks;
use Drupal\Tests\UnitTestCase;
use Drupal\views\ViewExecutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(JpfViewsHooks::class)]
#[Group('Custom')]
#[TestDox('Views: JpfViewsHooks unit')]
final class JpfViewsHooksTest extends UnitTestCase {

  private JpfViewsHooks $hooks;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    $this->hooks = new JpfViewsHooks();
  }

  #[Test]
  #[TestDox('viewsDataAlter() adds custom_last_date, custom_best_day and delta fields to each stats table')]
  public function viewsDataAlterAddsThreeFieldsToEachStatsTable(): void {
    $data = [];

    $this->hooks->viewsDataAlter($data);

    foreach (SchemaInterface::LOTTO_STATS_TABLES as $table_name) {
      $this->assertArrayHasKey('custom_last_date', $data[$table_name], "$table_name has custom_last_date.");
      $this->assertArrayHasKey('custom_best_day', $data[$table_name], "$table_name has custom_best_day.");
      $this->assertArrayHasKey('delta', $data[$table_name], "$table_name has delta.");
    }
  }

  #[Test]
  #[TestDox('viewsDataAlter() sets the correct field id for custom_last_date')]
  public function viewsDataAlterSetsCorrectFieldIdForCustomLastDate(): void {
    $data = [];

    $this->hooks->viewsDataAlter($data);

    $table = SchemaInterface::LOTTO_STATS_BALLS_TABLE;
    $this->assertSame('custom_last_date', $data[$table]['custom_last_date']['field']['id']);
  }

  #[Test]
  #[TestDox('viewsDataAlter() sets the correct field id for custom_best_day')]
  public function viewsDataAlterSetsCorrectFieldIdForCustomBestDay(): void {
    $data = [];

    $this->hooks->viewsDataAlter($data);

    $table = SchemaInterface::LOTTO_STATS_BALLS_TABLE;
    $this->assertSame('custom_best_day', $data[$table]['custom_best_day']['field']['id']);
  }

  #[Test]
  #[TestDox('viewsDataAlter() sets the correct field id for delta')]
  public function viewsDataAlterSetsCorrectFieldIdForDelta(): void {
    $data = [];

    $this->hooks->viewsDataAlter($data);

    $table = SchemaInterface::LOTTO_STATS_BALLS_TABLE;
    $this->assertSame('delta', $data[$table]['delta']['field']['id']);
  }

  #[Test]
  #[TestDox('viewsPreRender() returns early when view id does not start with lotto_stats')]
  public function viewsPreRenderReturnsEarlyWhenViewIdDoesNotMatch(): void {
    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn('other_view');
    $view->element = [];

    $this->hooks->viewsPreRender($view);

    $this->assertArrayNotHasKey('#attached', $view->element);
  }

  #[Test]
  #[TestDox('viewsPreRender() appends jpf_views library when view id starts with lotto_stats')]
  public function viewsPreRenderAppendsLibraryWhenViewIdMatches(): void {
    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn('lotto_stats_balls');
    $view->element = [];

    $this->hooks->viewsPreRender($view);

    $this->assertContains('jpf_views/jpf_views', $view->element['#attached']['library']);
  }

  #[Test]
  #[TestDox('viewsPreRender() returns early when view id() returns null')]
  public function viewsPreRenderReturnsEarlyWhenIdIsNull(): void {
    $view = $this->createMock(ViewExecutable::class);
    $view->method('id')->willReturn(NULL);
    $view->element = [];

    $this->hooks->viewsPreRender($view);

    $this->assertArrayNotHasKey('#attached', $view->element);
  }

}
