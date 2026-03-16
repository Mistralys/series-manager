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
