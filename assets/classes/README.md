# Core Module

Central application infrastructure for Series Manager.

## Responsibility

This module contains the foundational classes that bootstrap, route, authenticate, and render the application. `Manager` is the singleton entry point that every other component depends on. `SeriesCollection` manages the master list of TV series persisted in `data/series.json`.

## Key Concepts

- **Singleton Manager:** `Manager::getInstance()` is the central registry — it owns routing, session state, authentication, localization, TheTVDB client creation, and the series collection.
- **Page-Based Routing:** No framework router. `Manager::start()` reads the `page` request parameter and includes the matching PHP file from `pages/`.
- **Output Buffering:** Page content is captured via `ob_start()`/`ob_get_clean()` and injected into the HTML frame (`pages/_frame.php`).
- **JSON Persistence:** All data lives in JSON files on disk — no database.

## Directory Contents

| File | Role |
|---|---|
| `Manager.php` | Singleton: routing, session, auth, TheTVDB client, URL generation |
| `ManagerException.php` | Application-level exception |
| `SeriesCollection.php` | Loads/saves `data/series.json`, manages the `Series[]` collection |
| `UI.php` | Static HTML utility helpers (icon rendering) |
| `FormHandler.php` | Wrapper around HTML_QuickForm2 + Bootstrap3Renderer |
| `Bootstrap3Renderer.php` | Custom QuickForm2 renderer for Bootstrap 3 markup |

## Subdirectories

| Directory | Module |
|---|---|
| `Manager/` | Library indexer subsystem |
| `Series/` | Series entity and related domain objects |

## Integration Points

- **Inbound:** `index.php` calls `Manager::getInstance()->start()` to boot the application.
- **Outbound:** `Manager` creates and caches the TheTVDB API client, provides the `SeriesCollection`, and delegates to page files.
- **Series module** depends on `SeriesCollection` for persistence and `Manager` for URL generation and redirects.
- **Library module** depends on `Manager` for configuration constants (`APP_LIBRARY_PATHS`).
