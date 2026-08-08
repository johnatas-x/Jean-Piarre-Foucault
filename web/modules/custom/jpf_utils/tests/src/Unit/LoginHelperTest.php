<?php

declare(strict_types=1);

namespace Drupal\Tests\jpf_utils\Unit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\jpf_utils\LoginHelper;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(LoginHelper::class)]
#[Group('Custom')]
#[TestDox('Utils: LoginHelper unit')]
final class LoginHelperTest extends UnitTestCase {

  #[Test]
  #[TestDox('afterUserLogin() calls setRedirectUrl on form_state with a front Url')]
  public function afterUserLoginCallsSetRedirectUrlWithFrontRoute(): void {
    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('setRedirectUrl')
      ->with($this->callback(
        static fn (mixed $url): bool => $url instanceof Url && $url->getRouteName() === '<front>',
      ));

    LoginHelper::afterUserLogin([], $form_state);
  }

}
