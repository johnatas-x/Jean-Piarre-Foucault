<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\jpf_home\Services\HomepageHelperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Controller for site homepage.
 */
class HomepageController extends ControllerBase {

  use AutowireTrait;

  /**
   * HomepageController constructor.
   *
   * @param \Drupal\jpf_home\Services\HomepageHelperInterface $homepageHelper
   *   The homepage helper service.
   */
  public function __construct(
    #[Autowire(service: 'jpf_home.helper')]
    protected HomepageHelperInterface $homepageHelper,
  ) {
  }

  /**
   * Page content.
   *
   * @return array<string, array<string, int|list<int|null>|null>|\Drupal\Core\StringTranslation\TranslatableMarkup|string>
   */
  public function content(): array {
    return [
      '#theme' => 'homepage',
      '#title' => $this->t('Jean-Piarre Foucault'),
      '#last_draw' => $this->homepageHelper->getLastDraw(),
      '#last_predict' => $this->homepageHelper->getLastPredict(),
    ];
  }

}
