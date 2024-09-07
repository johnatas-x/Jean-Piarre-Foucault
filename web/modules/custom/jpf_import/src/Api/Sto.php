<?php

declare(strict_types=1);

namespace Drupal\jpf_import\Api;

use Drupal\jpf_store\Enum\Versions;

/**
 * Use of the FDJ private API "Sto" (thanks noobs !).
 */
abstract class Sto {

  /**
   * The initial default letter token identifier.
   */
  public final const string DEFAULT_LETTER_IDENTIFIER = 'k';

  /**
   * Custom token to make token dynamic.
   */
  private const string TOKENIZED_TOKEN = '{version_letter_identifier}';

  /**
   * API URL.
   */
  private const string URL = "https://www.sto.api.fdj.fr";

  /**
   * API user identification.
   */
  private const string USER = 'anonymous';

  /**
   * API draw service.
   */
  private const string SERVICE = 'service-draw-info';

  /**
   * API version.
   */
  private const string VERSION = 'v3';

  /**
   * Type of data.
   */
  private const string TYPE = 'documentations';

  /**
   * Parts of their built token !
   */
  private const array TOKEN_PARTS = [
    '1a2b3c4d',
    '9876',
    '4562',
    'b3fc',
    '2c963f66af' . self::TOKENIZED_TOKEN . '6',
  ];

  /**
   * Build URL to download FDJ loto file.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The file version.
   *
   * @return string
   *   The download URL.
   */
  public function buildDownloadUrl(Versions $version): string {
    return implode(
      '/',
      [
        self::URL,
        self::USER,
        self::SERVICE,
        self::VERSION,
        self::TYPE,
        $this->buildToken($version),
      ]
    );
  }

  /**
   * Build API token for the given version.
   *
   * @param \Drupal\jpf_store\Enum\Versions $version
   *   The version.
   *
   * @return string
   *   The token.
   */
  private function buildToken(Versions $version): string {
    $token = implode('-', self::TOKEN_PARTS);

    return str_replace(self::TOKENIZED_TOKEN, $version->letterIdentifier(), $token);
  }

}
