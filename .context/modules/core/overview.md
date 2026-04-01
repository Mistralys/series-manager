# Core - Overview
_SOURCE: Module overview_
# Module overview
```
// Structure of documents
└── assets/
    └── classes/
        └── Manager/
            ├── README.md
        └── README.md
        └── Series/
            └── README.md

```
###  Path: `\assets\classes\Manager/README.md`

```md
# Library Module

Local video file indexing and episode detection subsystem.

## Responsibility

Scans user-configured disk paths for video files, builds an index in `cache/library.json`, and provides lookup methods to match a series episode to a file on disk. Also manages name aliases that map non-standard filenames to recognized series names.

## Key Concepts

- **Manual Index Build:** The library index is resource-heavy and must be triggered explicitly by the user through the Library UI. It is never run automatically.
- **Filename Parsing:** Series names are extracted from filenames by normalizing (lowercase, strip punctuation) and detecting the `S##E##` episode marker pattern. Everything before the marker becomes the series name.
- **Name Aliases:** When a filename yields an unrecognized series name, the user adds an alias in `data/name-aliases.json` to map it to the correct series name.
- **Supported Extensions:** `mov`, `mpg`, `mpeg`, `mp4`, `avi`, `qt`, `mkv`.

## Directory Contents

| File | Role |
|---|---|
| `Library.php` | Main indexer — scans disk, manages cache, parses filenames, handles aliases |
| `LibraryException.php` | Library-specific exception |
| `LibraryFile.php` | Value object for one indexed video file (name, season, episode, path) |
| `LibrarySubfolder.php` | Value object for one subfolder in a library path |

## Integration Points

- **Inbound:** `pages/library.php` and its tab partials drive the Library UI.
- **Outbound:** `Library::findEpisode()` is called by `Episode::findInLibrary()` to check if an episode exists on disk.
- **Configuration:** Library paths come from the `APP_LIBRARY_PATHS` constant in `config-local.php`.

```
###  Path: `\assets\classes/README.md`

```md
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

```
###  Path: `\assets\classes\Series/README.md`

```md
# Series Module

TV series domain model — entity classes for Series, Season, and Episode, plus form and list rendering.

## Responsibility

Defines the data model for tracked TV series. A `Series` holds basic metadata (name, IMDB/TVDB IDs, archived/favorite flags) and, when fetched from TheTVDB, a hierarchy of `Season` and `Episode` objects. `SeriesForm` handles add/edit form rendering and POST processing. `SeriesList` renders the series overview with inline watching-progress tracking.

## Key Concepts

- **Flat JSON Storage:** Each series is an associative array in `data/series.json`. TheTVDB-fetched data (seasons, episodes) is nested under the `stored-info` key.
- **TheTVDB Integration:** `Series::fetchData()` calls the TheTVDB API, caches the response in `cache/{imdbID}-info.json`, and merges it into the series data.
- **Episode Completion:** An episode is "complete" if it is found in the Library index via `Episode::findInLibrary()`. A season is complete when all its episodes are found.
- **Watching Progress:** Tracked via `lastDLSeason`/`lastDLEpisode` integers, updated from the list view.

## Directory Contents

| File | Role |
|---|---|
| `Series.php` | TV series entity — data model, TVDB fetch, URL generation, status queries |
| `Season.php` | Season entity (child of Series) — episode collection, completion checks |
| `Episode.php` | Episode entity (child of Season) — disk detection, download status |
| `SeriesForm.php` | Add/edit form rendering and POST handling |
| `SeriesList.php` | Series overview list rendering and bulk update handling |

## Integration Points

- **Inbound:** `SeriesCollection` creates `Series` instances from `data/series.json`. Pages (`add.php`, `edit.php`, `list.php`, `archived.php`) use `SeriesForm` and `SeriesList`.
- **Outbound:** `Series::fetchData()` calls the TheTVDB API client from `Manager`. `Episode::findInLibrary()` queries the Library module for disk presence.

```
---
**File Statistics**
- **Size**: 6.09 KB
- **Lines**: 126
File: `modules/core/overview.md`
