<?php

declare(strict_types=1);

namespace Drupal\jpf_home\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\jpf_home\Services\HomepageHelperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Attribute\Route;

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
  ) {}

  /**
   * Page content.
   *
   * @return array<string, mixed>
   *   The page content renderer array.
   */
  #[Route(
    path: '/homepage',
    name: 'jpf_home.homepage',
    requirements: ['_access' => 'TRUE'],
    defaults: ['_title' => 'Jean-Piarre Foucault'],
  )]
  public function content(): array {
    return [
      '#theme' => 'homepage',
      '#title' => $this->t('Jean-Piarre Foucault'),
      '#last_draw' => $this->homepageHelper->getLastData('draw'),
      '#last_predict' => $this->homepageHelper->getLastData('prediction', 'draw_id'),
      '#next_predict' => $this->homepageHelper->nextPrediction(),
      '#attached' => [
        'library' => [
          'jpf_home/jpf_home',
        ],
      ],
      '#cache' => [
        'tags' => [
          'homepage_data',
        ],
      ],
    ];
  }

}
