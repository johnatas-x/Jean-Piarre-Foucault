<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for site homepage.
 */
class HomepageController extends ControllerBase {

  /**
   * Page content.
   *
   * @return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup|string|int|null>
   */
  public function content(): array {
    $variables = [
      'title' => $this->t('Work in progress'),
      'description' => $this->t('Coming soon...'),
    ];

    return [
      '#theme' => 'homepage',
      '#title' => $variables['title'],
      '#description' => $variables['description'],
    ];
  }

}
