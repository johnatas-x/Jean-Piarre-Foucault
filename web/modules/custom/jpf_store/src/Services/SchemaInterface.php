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
   * The name of the table which contains all versions information.
   */
  final public const string LOTTO_VERSIONS = 'lotto_versions';

  /**
   * The name of the table which contains all balls lotto stats.
   */
  final public const string LOTTO_STATS_BALLS_TABLE = 'lotto_balls_stats';

  /**
   * The name of the table which contains all lucky balls lotto stats.
   */
  final public const string LOTTO_STATS_LUCKY_TABLE = 'lotto_lucky_stats';

  /**
   * List of stats tables with description.
   */
  final public const array LOTTO_STATS_TABLES = [
    'balls' => self::LOTTO_STATS_BALLS_TABLE,
    'lucky balls' => self::LOTTO_STATS_LUCKY_TABLE,
  ];

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

  /**
   * Version stats schema fields.
   *
   * @return array<string, array<string, bool|int|string>>
   *   The fields.
   */
  public function versionStatsFields(): array;

}