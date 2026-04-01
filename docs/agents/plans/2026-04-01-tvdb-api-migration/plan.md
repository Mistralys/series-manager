# Plan — Migrate from `adrenth/thetvdb2` to `canihavesomecoffee/thetvdbapi`

## Summary

Replace the deprecated `adrenth/thetvdb2` (TheTVDB API v2/v3) dependency with `canihavesomecoffee/thetvdbapi` (TheTVDB API v4). This requires updating Composer configuration, rewriting the API client initialization in `Manager`, rewriting the data-fetching and data-mapping logic in `Series`, updating the dump/debug mode, and verifying that the cached JSON data shape remains compatible or is migrated gracefully. No architectural changes are needed — the app stays a single-runtime PHP application with JSON file persistence.

## Architectural Context

The TheTVDB integration is confined to a narrow surface area:

| File | Role |
|---|---|
| `assets/classes/Manager.php` | `createClient()` — instantiates `Adrenth\Thetvdb\Client`, authenticates with `APP_API_KEY` + `APP_SUBSCRIBER_PIN`, caches for request lifetime. Return type: `Adrenth\Thetvdb\Client`. |
| `assets/classes/Series/Series.php` | `fetchData(Client $client, …)` — calls `$client->series()->get($id)` and `$client->series()->getEpisodes($id, $page)` to populate the `stored-info` blob. Uses `Adrenth\Thetvdb\Model\BasicEpisode` type. `episode2array()` helper converts a `BasicEpisode` to an array. Debug/dump mode uses `$client->performApiCallWithJsonResponse()`. |
| `assets/classes/SeriesCollection.php` | `fetchData()` — calls `Manager::createClient()` and delegates to each `Series::fetchData()`. |
| `pages/edit.php` | Calls `$manager->createClient()` and `$selected->fetchData($client, …)`. |
| `pages/fetch.php` | Calls `$series->fetchData($clearCache)` (which is `SeriesCollection::fetchData()`). |
| `composer.json` | Declares `adrenth/thetvdb2 >=6.2` dependency and a VCS repository pointing to `https://github.com/Mistralys/thetvdb2.git`. |
| `config-local.dist.php` / `config-local.php` | Defines `APP_API_KEY` and `APP_SUBSCRIBER_PIN` constants. |

**Cached data shape** (`cache/{imdbID}-info.json`):

```json
{
  "status": "string",
  "genre": ["string"],
  "network": "string",
  "overview": "string",
  "season": "int",
  "firstAired": "string",
  "siteRating": "float",
  "siteRatingCount": "int",
  "seasons": {
    "1": {
      "id": "int (TVDB season ID)",
      "episodes": {
        "1": { "id": "int", "name": "string", "overview": "string" },
        ...
      }
    },
    ...
  }
}
```

**New library model mapping** (`canihavesomecoffee/thetvdbapi`):

| Old (adrenth) | New (canihavesomecoffee) |
|---|---|
| `new Client()` | `new TheTVDbAPI()` |
| `$client->authentication()->login($apiKey, null, $pin)` → returns token string | `$api->authentication()->login($apiKey, $pin)` → returns token string |
| `$client->setToken($token)` | `$api->setToken($token)` |
| `$client->setLanguage('en')` | Constructor arg: `new TheTVDbAPI('eng')` |
| `$client->series()->get($id)` → returns model with `getStatus()`, `getGenre()`, `getNetwork()`, etc. | `$api->series()->simple($id)` → returns `SeriesBaseRecord` with public properties `$status` (Status object), `$firstAired`, `$name`, `$score`, etc. Genre/network are NOT on `SeriesBaseRecord`; they require `$api->series()->extended($id)` → `SeriesExtendedRecord` which has `$genres` (GenreBaseRecord[]), `$originalNetwork` / `$latestNetwork` (Company), `$overview`. |
| `$client->series()->getEpisodes($id, $page)->getData()` → `Collection<BasicEpisode>` | `$api->series()->allEpisodes($id)` → `EpisodeBaseRecord[]` (auto-paginates). Or `$api->series()->episodes($id, …)` for manual pagination. |
| `BasicEpisode::getAiredSeason()` | `EpisodeBaseRecord::$seasonNumber` |
| `BasicEpisode::getAiredEpisodeNumber()` | `EpisodeBaseRecord::$number` |
| `BasicEpisode::getAiredSeasonID()` | Not directly available per-episode; `SeasonBaseRecord::$id` from `SeriesExtendedRecord::$seasons`. |
| `BasicEpisode::getId()` | `EpisodeBaseRecord::$id` |
| `BasicEpisode::getEpisodeName()` | `EpisodeBaseRecord::$name` |
| `BasicEpisode::getOverview()` | `EpisodeBaseRecord::$overview` |
| `$client->performApiCallWithJsonResponse(…)` (dump mode) | `$api->performAPICallWithJsonResponse(…)` (same concept, different casing) |

## Approach / Architecture

1. **Dependency swap** — Replace Composer dependency & remove VCS repository.
2. **Client factory rewrite** — Rewrite `Manager::createClient()` to instantiate `TheTVDbAPI`, authenticate, return the new client type.
3. **Data-fetch rewrite** — Rewrite `Series::fetchData()` to use `$api->series()->extended($id)` for series metadata (gets genres + network in one call), and `$api->series()->allEpisodes($id)` for episode data. Map the new DTOs to the existing `stored-info` JSON shape.
4. **Episode helper rewrite** — Rewrite `Series::episode2array()` to accept `EpisodeBaseRecord`.
5. **Dump mode rewrite** — Update the dump/debug branch to use the new client's `performAPICallWithJsonResponse()`.
6. **Season ID mapping** — Build a season-number→season-ID lookup from `SeriesExtendedRecord::$seasons` and inject it when building the seasons array, since the new episode object doesn't carry a season ID directly.
7. **Config constants** — No changes needed; `APP_API_KEY` and `APP_SUBSCRIBER_PIN` are still valid for the v4 API.
8. **Cache compatibility** — The `stored-info` JSON shape does not change. Existing cache files remain valid. Two fields with changed semantics:
   - `status`: was a plain string, now derived from `Status::$name` (still a string).
   - `genre`: was `string[]`, now derived from `GenreBaseRecord[]` → map to `string[]` via `$genre->name`.
   - `network`: was a string, now derived from `Company::$name`.
   - `siteRating` / `siteRatingCount`: the v4 API no longer provides these fields. They will be replaced by `score` (a single float from `SeriesBaseRecord::$score`). The `siteRatingCount` key will be dropped from new fetches. Existing cache files that still have these keys are harmless since `Series::getInfo()` reads keys on-demand.
9. **Manifest updates** — Update `tech-stack.md`, `api-surface.md`, `constraints.md`, and `data-flows.md` to reflect the new dependency and method signatures.

## Rationale

- **`canihavesomecoffee/thetvdbapi`** is the only actively maintained PHP library for TheTVDB API v4 (latest release: Dec 2025, 60 releases, 21k+ downloads).
- The `allEpisodes()` method in the new library auto-paginates, eliminating the manual page-loop in `fetchData()`.
- Using `series()->extended($id)` in a single call gives genres, network, overview, seasons, and status — fewer HTTP round-trips than the old library.
- The cached JSON shape is preserved, so existing `cache/*.json` files remain valid after migration (no forced re-fetch).
- No architectural changes are needed. The integration surface is small (3 PHP files + 2 page files).

## Detailed Steps

### Step 1 — Composer dependency swap

1. In `composer.json`:
   - Remove the `repositories` entry for `https://github.com/Mistralys/thetvdb2.git`.
   - Replace `"adrenth/thetvdb2": ">=6.2"` with `"canihavesomecoffee/thetvdbapi": "^2.1"` in `require`.
   - Remove `"ext-simplexml": "*"` (no longer needed; the new library uses Symfony Serializer, not XML).
2. Run `composer update` to install the new package and remove the old one.
3. Run `composer dump-autoload` to regenerate the classmap.

### Step 2 — Rewrite `Manager::createClient()`

**File:** `assets/classes/Manager.php`

- Change the `use` import from `Adrenth\Thetvdb\Client` to `CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI`.
- Change the `$client` property type from `?Client` to `?TheTVDbAPI`.
- Change the return type of `createClient()` from `Client` to `TheTVDbAPI`.
- Rewrite the method body:
  ```php
  public function createClient() : TheTVDbAPI
  {
      if (isset($this->client)) {
          return $this->client;
      }

      $client = new TheTVDbAPI('eng');
      $token = $client->authentication()->login(APP_API_KEY, APP_SUBSCRIBER_PIN);
      $client->setToken($token);

      $this->client = $client;
      return $client;
  }
  ```

### Step 3 — Rewrite `Series::fetchData()`

**File:** `assets/classes/Series/Series.php`

- Replace `use Adrenth\Thetvdb\Client` with `use CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI`.
- Replace `use Adrenth\Thetvdb\Model\BasicEpisode` with `use CanIHaveSomeCoffee\TheTVDbAPI\Model\EpisodeBaseRecord`.
- Change the `$client` parameter type in `fetchData()` from `Client` to `TheTVDbAPI`.
- Rewrite the main fetch body:
  ```php
  $extended = $client->series()->extended((int)$id);

  $info = array(
      self::INFO_STATUS => $extended->status->name ?? '',
      self::INFO_GENRE => array_map(fn($g) => $g->name, $extended->genres),
      self::INFO_NETWORK => $extended->originalNetwork->name ?? ($extended->latestNetwork->name ?? ''),
      self::INFO_OVERVIEW => $extended->overview ?? '',
      self::INFO_SEASON => count($extended->seasons),
      self::INFO_FIRST_AIRED => $extended->firstAired ?? '',
      self::INFO_SITE_RATING => $extended->score,
      self::INFO_SITE_RATING_COUNT => 0,
      self::INFO_SEASONS => array()
  );

  // Build season number → season ID lookup from extended record
  $seasonIdMap = [];
  foreach ($extended->seasons as $seasonRecord) {
      // SeasonBaseRecord has $number and $id
      $seasonIdMap[$seasonRecord->number] = $seasonRecord->id;
  }

  $episodes = $client->series()->allEpisodes((int)$id);
  foreach ($episodes as $episode) {
      $season = $episode->seasonNumber;
      if (!isset($info[self::INFO_SEASONS][$season])) {
          $info[self::INFO_SEASONS][$season] = array(
              self::INFO_SEASON_ID => $seasonIdMap[$season] ?? 0,
              self::INFO_SEASON_EPISODES => array()
          );
      }
      $info[self::INFO_SEASONS][$season][self::INFO_SEASON_EPISODES][$episode->number] = $this->episode2array($episode);
  }
  ```

### Step 4 — Rewrite `Series::episode2array()`

```php
private function episode2array(EpisodeBaseRecord $episode) : array
{
    return array(
        self::INFO_EPISODE_ID => $episode->id,
        self::INFO_EPISODE_NAME => $this->filterName($episode->name),
        self::INFO_EPISODE_OVERVIEW => $episode->overview ?? ''
    );
}
```

### Step 5 — Rewrite the dump/debug branch in `fetchData()`

Replace the old `performApiCallWithJsonResponse` calls with the new client's equivalent:

```php
if ($dump) {
    header('Content-Type: text/plain');

    $json = $client->performAPICallWithJsonResponse('get', 'series/' . (int)$id . '/extended');
    print_r($json);

    $json = $client->performAPICallWithJsonResponse('get', 'series/' . (int)$id . '/episodes/default/eng');
    print_r($json);

    exit;
}
```

### Step 6 — Update `SeriesCollection::fetchData()` parameter type

**File:** `assets/classes/SeriesCollection.php`

No changes needed. `SeriesCollection::fetchData()` calls `Manager::createClient()` which now returns the new type, and passes it to `Series::fetchData()` which also expects the new type. The parameter flow is already consistent.

### Step 7 — Update `pages/edit.php`

No changes to the page file itself. It calls `$manager->createClient()` and passes the result to `$selected->fetchData()`. Since both signatures are updated, the page file works as-is.

### Step 8 — Verify `ext-simplexml` removal is safe

Grep the codebase for any other usage of SimpleXML. If none exists outside the old thetvdb2 dependency, it is safe to remove from `composer.json`.

### Step 9 — Update project manifest documents

- **`tech-stack.md`**: Replace `adrenth/thetvdb2 >=6.2` → `canihavesomecoffee/thetvdbapi ^2.1`. Update role description. Remove `ext-simplexml` if safe. Remove mention of "custom VCS fork".
- **`api-surface.md`**: Change `Manager::createClient()` return type from `\Adrenth\Thetvdb\Client` to `\CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI`. Change `Series::fetchData()` parameter type similarly. Update `episode2array` parameter type.
- **`data-flows.md`**: Update flow §4 ("Fetch Series Data from TheTVDB") to reference `$client->series()->extended()` and `$client->series()->allEpisodes()` instead of `$client->series()->get()` and `$client->series()->getEpisodes()`.
- **`constraints.md`**: Update the "TheTVDB Integration" section — remove mention of VCS fork, update package name.

### Step 10 — Update changelog

Add an entry to `changelog.md` under a new version documenting the API migration.

## Dependencies

- `canihavesomecoffee/thetvdbapi ^2.1` (adds transitive deps: `symfony/serializer`, `symfony/property-access`, `symfony/property-info`, `guzzlehttp/guzzle`).
- Removal of `adrenth/thetvdb2` and its VCS repository.

## Required Components

| Component | Action |
|---|---|
| `composer.json` | Edit: swap dependency, remove VCS repo |
| `assets/classes/Manager.php` | Edit: rewrite `createClient()` |
| `assets/classes/Series/Series.php` | Edit: rewrite `fetchData()`, `episode2array()`, dump branch, imports |
| `docs/agents/project-manifest/tech-stack.md` | Edit: update dependency table |
| `docs/agents/project-manifest/api-surface.md` | Edit: update return/param types |
| `docs/agents/project-manifest/data-flows.md` | Edit: update flow §4 |
| `docs/agents/project-manifest/constraints.md` | Edit: update TheTVDB section |
| `changelog.md` | Edit: add migration entry |

## Assumptions

- TheTVDB API v4 authentication still accepts `apiKey` + `subscriberPin`. The `APP_API_KEY` and `APP_SUBSCRIBER_PIN` constants remain valid.
- The `canihavesomecoffee/thetvdbapi ^2.1` package is compatible with PHP 8.4 (its `composer.json` requires `>=7.4`).
- The `series()->extended()` endpoint returns genres and network data in a single call, avoiding a second HTTP request.
- The `allEpisodes()` method handles pagination internally, removing the need for the manual page loop.
- The `SeasonBaseRecord` objects in `SeriesExtendedRecord::$seasons` contain a `$number` property that maps to the aired season number, and an `$id` property for the TVDB season ID.

## Constraints

- The cached JSON shape (`cache/{imdbID}-info.json`) must remain backward-compatible. Existing cache files should not cause crashes.
- No database may be introduced.
- No Node.js middleware or additional runtime may be introduced.
- `declare(strict_types=1)` must be present on every modified PHP file.
- The `ext-simplexml` dependency should only be removed after confirming no other code references it.

## Out of Scope

- Migrating existing cache files to a new format (they remain valid as-is).
- Adding new API features (artwork, search, updates) beyond what is currently used.
- Upgrading Bootstrap or jQuery.
- Adding PSR-6/PSR-16 caching to the API client (the app uses its own file-based cache).
- Creating an abstraction layer / interface for the API client. The surface area is too small to justify it.

## Acceptance Criteria

1. `composer install` succeeds without errors, and `adrenth/thetvdb2` is no longer in `vendor/`.
2. `Manager::createClient()` returns a `CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI` instance.
3. Single-series fetch (`pages/edit.php` with `?fetch=yes`) successfully retrieves data from TheTVDB API v4, writes `cache/{imdbID}-info.json`, and populates the edit/seasons tab.
4. Bulk fetch (`pages/fetch.php` with `?confirm=yes`) successfully iterates all series.
5. Existing `cache/{imdbID}-info.json` files (from the old library) are loaded without error.
6. The dump/debug mode (`?dump=yes`) outputs raw API v4 JSON.
7. PHPStan analysis passes at the current level.
8. All existing PHPUnit tests pass.

## Testing Strategy

1. **Manual smoke test — single fetch:** Add or select a series with a known TVDB ID, clear cache, fetch data, verify the seasons/episodes tab populates correctly.
2. **Manual smoke test — bulk fetch:** Run the bulk fetch page, verify all series with TVDB IDs receive data.
3. **Manual smoke test — cache backward compatibility:** Keep an existing `cache/ttXXXX-info.json` file from before the migration, load the series edit page, verify no errors.
4. **Manual smoke test — dump mode:** Trigger `?dump=yes` on a series edit page, verify raw JSON output.
5. **PHPUnit:** Run `composer exec phpunit` — all existing tests should pass (they test library indexing, not API calls).
6. **PHPStan:** Run `vendor/bin/phpstan analyse` — verify no new errors from the migration.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`canihavesomecoffee/thetvdbapi` drops PHP 8.4 support or becomes unmaintained** | The library has been actively maintained for 9 years with 60 releases. If it dies, the fallback is a custom Guzzle wrapper (small surface area). |
| **`SeriesExtendedRecord::$seasons` doesn't include season numbers, breaking the season-ID lookup** | Verify by inspecting the `SeasonBaseRecord` model. If `$number` is missing, fall back to using the season number from episode data and set season ID to 0. |
| **TheTVDB v4 API changes authentication model again** | The new library abstracts authentication; a version bump should handle it. No custom auth code is needed. |
| **`siteRating` / `siteRatingCount` removal breaks UI** | These fields are read via `Series::getInfo()` which returns null for missing keys. The UI should gracefully handle null/0 values. Verify in the edit summary tab. |
| **`ext-simplexml` is used elsewhere in the codebase** | Grep before removing. If found, keep it in `composer.json`. |
| **Guzzle version conflict with existing dependencies** | `canihavesomecoffee/thetvdbapi` requires `guzzlehttp/guzzle ^7.0`. Verify no conflicting constraint exists in the dependency tree. |
