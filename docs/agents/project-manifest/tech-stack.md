# Tech Stack & Patterns

## Runtime & Language

| Item | Value |
|---|---|
| **Language** | PHP >= 8.4 (strict_types enforced on every file) |
| **Runtime** | Local web server (Apache / Nginx / built-in PHP server) |
| **Dependency Manager** | Composer |
| **Autoloader** | Composer classmap — `assets/classes/` (production), `tests/classes/` (dev) |
| **Minimum Stability** | `dev` (prefer-stable: true) |

## Production Dependencies

| Package | Version Constraint | Role |
|---|---|---|
| `mistralys/application-utils` | `^3.0` | File helpers (`FileInfo`, `FolderInfo`, `JSONFile`), request handling (`Request`), output buffering, string/array utilities |
| `mistralys/application-localization` | `^1.4` | Multi-locale support (`Localization`, `t()`, `pt()`, `pts()`, `ptex()`) |
| `mistralys/changelog-parser` | `^1.0` | Reads `changelog.md` to auto-update `version.txt` at boot |
| `mistralys/html_quickform2` | `^2.3` | HTML form building (login form) |
| `canihavesomecoffee/thetvdbapi` | `^2.1` | TheTVDB v4 API client |
| `ext-json` | `*` | JSON encode/decode (library cache, data files) |

## Development Dependencies

| Package | Role |
|---|---|
| `phpunit/phpunit >= 9.6.9` | Unit testing |
| `phpstan/phpstan >= 1.10.20` | Static analysis |
| `roave/security-advisories` | Blocks packages with known CVEs |

## Frontend

| Item | Value |
|---|---|
| **CSS framework** | Bootstrap 3 (`bootstrap.min.css`) |
| **Dark mode** | Bootswatch theme (`bootswatch.min.css`) toggled via session |
| **JavaScript** | jQuery 1.11.2 + Bootstrap 3 JS (minimal custom JS in `js/add.js`, `js/list.js`) |
| **Icons** | Bootstrap 3 Glyphicons |
| **Font assets** | Stored in `fonts/` (Bootstrap fonts) |

## Architectural Patterns

### Page-Based Routing (No MVC Framework)
There is no router or MVC framework. `Manager::start()` reads the `page` GET/POST parameter, includes the matching file from `pages/`, and wraps it in the HTML frame (`pages/_frame.php`).

### Singleton Manager
`Manager` is a singleton (`Manager::getInstance()`). It is the central registry: it holds the `SeriesCollection`, the TheTVDB API client, request/session state, locale, and the page-routing logic.

### Data Persistence via JSON Files
All persistent data is stored as JSON files on disk. There is no database.

| File | Contents |
|---|---|
| `data/series.json` | Master list of all series |
| `data/name-aliases.json` | Library file name → series name mappings |
| `cache/library.json` | Indexed library file list (built manually) |
| `cache/{imdbID}-info.json` | Per-series data fetched from TheTVDB |

### Output Buffering Pattern
Pages are rendered with PHP `ob_start()` / `ob_get_clean()` inside `Manager::start()`. The buffered content is injected into `pages/_frame.php` via `Manager::getContent()`.

### Localization
Uses `mistralys/application-localization`. Supported locales: `en_UK` (default), `de_DE`, `fr_FR`. Translation strings are stored in `localization/`. The active locale is persisted in `$_SESSION['locale']` and toggled via the `selectLocale` GET/POST parameter.

### Authentication
Session-based password authentication. The password is defined as the `APP_PASSWORD` constant in `config-local.php`. The session stores a salted SHA-1 hash of the password (`APP_SALT`). If `$_SESSION['auth']` does not match, all pages redirect to `login`.

### Context Documentation (CTX Generator)
The project uses the [CTX Generator](https://github.com/context-hub/generator) to produce auto-generated context documents for AI agents. Configuration lives in `context.yaml` (root) and `module-context.yaml` files in each module directory under `assets/classes/`. Generated output is written to `.context/` (gitignored). Regenerate with `ctx generate`.
