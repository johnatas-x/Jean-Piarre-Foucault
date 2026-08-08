<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_utils\Unit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\jpf_utils\Hook\JpfUtilsHooks;
use Drupal\jpf_utils\LoginHelper;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(JpfUtilsHooks::class)]
#[Group('Custom')]
#[TestDox('Utils: JpfUtilsHooks unit')]
final class JpfUtilsHooksTest extends UnitTestCase {

  private JpfUtilsHooks $hooks;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->hooks = new JpfUtilsHooks();
  }

  #[Test]
  #[TestDox('toolbarAlter() appends the jpf_utils/toolbar library to the admin_toolbar_tools item')]
  public function toolbarAlterAppendsJpfUtilsLibrary(): void {
    $items = ['admin_toolbar_tools' => ['#attached' => ['library' => []]]];

    $this->hooks->toolbarAlter($items);

    $this->assertContains('jpf_utils/toolbar', $items['admin_toolbar_tools']['#attached']['library']);
  }

  #[Test]
  #[TestDox('formUserLoginFormAlter() appends afterUserLogin callable to form #submit')]
  public function formUserLoginFormAlterAppendsSubmitCallback(): void {
    $form = ['#submit' => []];
    $form_state = $this->createMock(FormStateInterface::class);

    $this->hooks->formUserLoginFormAlter($form, $form_state, 'user_login_form');

    $this->assertContains([LoginHelper::class, 'afterUserLogin'], $form['#submit']);
  }

}
