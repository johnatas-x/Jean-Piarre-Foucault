<?php

declare(strict_types=1);

namespace Drupal\jpf_utils;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Helper static methods for login.
 */
class LoginHelper {

  /**
   * After login callback.
   *
   * @param array<mixed> $form
   *   The login form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The login form state.
   *
   * @SuppressWarnings("PHPMD.UnusedFormalParameter")
   */
  public static function afterUserLogin(array $form, FormStateInterface $form_state): void {
    $form_state->setRedirectUrl(Url::fromRoute('<front>'));
  }

}
