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
