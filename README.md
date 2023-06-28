# Series Manager

Web-based tool for keeping track of TV series watched and downloaded.

## Setup

1. Run `composer install`.
2. Copy `config-local.dist.php` to `config-local.php`.
3. Edit the configuration settings in the file.
4. Optional: Add custom search links (see below).
5. Open the project folder in a browser via the webserver.

## Local files library

### Indexing files

Whether episodes are available can be determined automatically
if the video files are present locally. The library handles this,
by indexing all files and cross-referencing them with the series
by name.

> Indexing is resource heavy, which is why it must be done manually.
> Whenever you add new videos, remember to update the index in the
> library screen.

### Files not recognized?

This can happen when the video's file name can not be converted 
into a meaningful series name, or if the episode information is
missing.

The library extracts the name like this:

1. Find the episode info, e.g. `S01E05`.
2. Use everything before this as the name.

```
Game.Of.Thrones.S01E05.This.Day.All.Gods.Die.mp4
                ^Episode info
```

This example will use the name `game of thrones`.

The automatically detected name can be adjusted in the Library 
UI in the "Name aliases" tab.

Example:

- Detected name: `got`.
- Enter alias: `game of thrones`.
- Refresh the index. 

## Customizing

### Adding custom search links

In the overview, as well as the series episodes list, it is
possible to show custom search links to search for the series'
name or episode number on external websites.

Create the file `search-links.json` in the project root folder,
then paste the following into it:

```json
[
  {
    "label": "Website A",
    "template": "https://example.website/?search={SEARCH}"
  },
  {
    "label": "Website B",
    "template": "https://example.website/?search={SEARCH}"
  }
]
```

You may add several websites. The `{SEARCH}` placeholder in the 
URL template is replaced by the series' or episode's name and
number.
