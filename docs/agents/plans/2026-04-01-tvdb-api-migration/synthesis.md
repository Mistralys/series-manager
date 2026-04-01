## Synthesis

### Completion Status
- Status: COMPLETE
- Completed by: Standalone Developer Agent

### Implementation Summary
- Swapped `adrenth/thetvdb2 >=6.2` (custom VCS fork) for `canihavesomecoffee/thetvdbapi ^2.1` in `composer.json`. Removed the VCS `repositories` entry and the `ext-simplexml` extension dependency.
- Ran `composer update` — the old package and its 14 transitive dependencies (illuminate/*, nesbot/carbon, symfony/translation, etc.) were removed; the new package and its transitive deps (symfony/property-access, symfony/property-info, symfony/string, phpdocumentor/*, webmozart/assert, doctrine/deprecations) were installed. Version: `canihavesomecoffee/thetvdbapi 2.1.12`.
- `Manager::createClient()` rewritten: uses `new TheTVDbAPI('eng')`, authenticates with `$client->authentication()->login(APP_API_KEY, APP_SUBSCRIBER_PIN)`, sets token via `$client->setToken()`.
- `Series::fetchData()` rewritten: single `$client->series()->extended($id)` call replaces the old `series()->get()` + page loop. `$client->series()->allEpisodes($id)` replaces the manual pagination loop (no more `try/catch` around each page). Season ID lookup is built from `SeriesExtendedRecord::$seasons` since `EpisodeBaseRecord` no longer carries a season ID directly.
- `Series::episode2array()` updated to accept `EpisodeBaseRecord` and access public properties (`$id`, `$name`, `$overview`, `$number`, `$seasonNumber`).
- Dump/debug branch updated to use `$client->performAPICallWithJsonResponse()` (was `performApiCallWithJsonResponse`; different casing in the new library). Now calls the `extended` and `episodes/default/eng` endpoints instead of the v2 paths.
- Removed unused `use` statements: `JSONConverter`, `Throwable` (no longer needed after the rewrite).
- `cache/{imdbID}-info.json` shape preserved. `siteRatingCount` is now always written as `0`; `siteRating` maps to `SeriesBaseRecord::$score`. Existing cache files remain valid.

### Documentation Updates
- `docs/agents/project-manifest/tech-stack.md`: Replaced `adrenth/thetvdb2 >=6.2` row with `canihavesomecoffee/thetvdbapi ^2.1`. Removed `ext-simplexml` row.
- `docs/agents/project-manifest/api-surface.md`: Updated `Manager::createClient()` return type and `Series::fetchData()` parameter type to reference `\CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI`.
- `docs/agents/project-manifest/constraints.md`: Updated TheTVDB Integration section — replaced package name, removed VCS fork mention.
- `docs/agents/project-manifest/data-flows.md`: Updated §4 to reference `series()->extended()` and `series()->allEpisodes()` calls.
- `changelog.md`: Added `v3.2.0` entry documenting the migration.

### Verification Summary
- Tests run: `vendor/bin/phpunit --testdox`
  - PASS: `IndexingTests > Normalize names` (1/3)
  - FAIL (pre-existing): `IndexingTests > Indexing` — asserts 2 library files but finds 5417; pre-existing environment issue with the `library-1` test fixture path resolving to the real media library. Not related to this migration.
  - FAIL (pre-existing): `IndexingTests > Find episode` — consequence of the same pre-existing fixture path issue.
- Static analysis run: `vendor/bin/phpstan analyse assets/classes/Manager.php assets/classes/Series/Series.php --level=5`
  - 8 errors reported — all are `APP_*` constant-not-found warnings (`APP_ROOT`, `APP_PASSWORD`, `APP_SALT`, `APP_API_KEY`, `APP_SUBSCRIBER_PIN`). These are pre-existing: the constants are defined in `config-local.php` which is a runtime include unavailable during static analysis. No new errors introduced by this migration.
- Result: No regressions introduced. The two test failures and all PHPStan warnings pre-date this change.

### Code Insights
- [low] (debt) `assets/classes/Series/Series.php` — `INFO_SITE_RATING_COUNT` constant and the corresponding `siteRatingCount` key in the JSON shape are now vestigial. The v4 API no longer provides a rating count; the field is always written as `0`. The constant could be deprecated or removed in a future cleanup pass alongside any UI that renders the count.
- [low] (improvement) `assets/classes/Series/Series.php::fetchData()` — The network fallback expression (`isset($extended->originalNetwork->name) ? ... : (isset($extended->latestNetwork->name) ? ... : '')`) is verbose. Both `originalNetwork` and `latestNetwork` are typed as `Company` (not nullable) in `SeriesExtendedRecord`, but may be uninitialized on parse. A small helper or a null-safe operator chain (`$extended->originalNetwork?->name ?? $extended->latestNetwork?->name ?? ''`) would be cleaner and more idiomatic for PHP 8.
- [low] (debt) `assets/classes/Series/Series.php` — `OutputBuffering` and `Request` are imported but only `Request` is used in this file; `OutputBuffering` appears to be unused. Worth investigating whether it can be removed.
- [medium] (debt) `tests/testsuites/Library/IndexingTests.php` — Two tests (`test_indexing`, `test_findEpisode`) are failing in the developer environment because `library-1` resolves to a path that contains thousands of real media files rather than the 2 expected test fixtures. The test fixture setup should use isolated temporary directories or fixtures checked into the repository to be environment-independent.

### Additional Comments
- The `allEpisodes()` method in the new library internally paginates using the `episodes()` route with a language code. If `TheTVDbAPI` is constructed without a secondary language (as in this implementation), only the primary language (`eng`) is fetched. This is the correct behavior for this application.
- Existing `cache/*.json` files do not need to be invalidated. They are forward-compatible: the old `siteRatingCount` value (if non-zero) is ignored on read, and the new `siteRatingCount: 0` is harmless.
