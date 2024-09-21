<?php

declare(strict_types=1);

namespace Drupal\jpf_store\Services;

/**
 * Provides an interface for all schema definitions.
 */
interface SchemaInterface {

  /**
   * The name of the table which contains all lotto draws.
   */
  final public const string LOTTO_DRAWS_TABLE = 'lotto_draws';

  /**
   * The name of the table which contains all lotto stats.
   */
  final public const string LOTTO_STATS_TABLE = 'lotto_stats';

  /**
   * Lotto draws schema fields.
   *
   * @return array<string, array<string, bool|int|string>>
   *   The fields.
   */
  public function lottoDrawsFields(): array;

  /**
   * Lotto stats schema fields.
   *
   * @return array<string, array<string, bool|int|string>>
   *   The fields.
   */
  public function lottoStatsFields(): array;

}
