<?php

declare(strict_types=1);

namespace Drupal\jpf_utils\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\jpf_utils\LoginHelper;

/**
 * Hook implementations for jpf_utils.
 */
class JpfUtilsHooks {

  /**
   * Implements hook_toolbar_alter().
   */
  #[Hook('toolbar_alter')]
  public function toolbarAlter(&$items): void {
    $items['admin_toolbar_tools']['#attached']['library'][] = 'jpf_utils/toolbar';
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * @SuppressWarnings("PHPMD.UnusedFormalParameter")
   */
  #[Hook('form_user_login_form_alter')]
  public function formUserLoginFormAlter(&$form, FormStateInterface $form_state, $form_id): void {
    $form['#submit'][] = [LoginHelper::class, 'afterUserLogin'];
  }

}
