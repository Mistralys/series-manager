# Series Manager

Web-based tool for keeping track of TV series watched and stored 
locally on disk, with optional [TheTVDB][] integration to fetch 
episode information.

## Features

- Keep a list of series, add links to TVDB and IMDB.
- Keep track of seasons and episodes watched.
- With a TVDB subscription, fetch season and episode details (optional).
- Detect which episodes match video files locally on the disk.
- Select favorites and archive older series.
- Add links to other services to search by name.
- UI in English, German and French.

## Requirements

- Local webserver with PHP 7.4+
- [Composer][]
- [TheTVDB][] API account

> NOTE: The tool is not intended to be used on a public server.
> It is meant to be used locally, e.g. on a Raspberry Pi, without
> access from the outside. It has not been tested for security.

## Setup

1. Create an account for an API key on [TheTVDB][].
2. Clone the project locally.
3. Run `composer install`.
4. Copy `config-local.dist.php` to `config-local.php`.
5. Edit the configuration settings in the file.
6. Optional: Add custom search links (see below).
7. Open the project folder in a browser via the webserver.
8. Log in with the password set in the configuration.

## Quick start

Once you have set up the project, you can start adding series
via the "Add new" menu. Simply fill in the required information,
and it will be added to the overview.

After adding a new series, you can fetch data from TheTVDB by
going into the series' detail view and clicking the "Fetch data"
button. The available seasons and episodes will become available.

In the overview, you can keep track of your watching progress
by entering the season and episode number you have watched.

## Local files library

### Indexing files

Whether episodes are available can be determined automatically
if the video files are present locally or in a mounted network
storage or NAS. The library handles this by indexing all files 
and cross-referencing them with the series by name.

> Indexing is resource heavy, which is why it must be done manually.
> Whenever you add new videos, remember to update the index in the
> library screen.

### Files not recognized?

This can happen when the video's file name can not be converted 
into a meaningful series name, or if the episode information is
missing.

The library extracts the name like this:

1. Normalize the file name to remove special characters.
2. Find the episode marker in the name, e.g. `S01E05`.
3. Use everything before this as the name.

```
Game.Of.Thrones.S01E05.This.Day.All.Gods.Die.mp4
                ^Episode marker
```

This example will give `game of thrones` as result.

To fix names that were not detected correctly, the auto-detected 
name can be adjusted in the Library UI in the "Name aliases" tab.

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

[TheTVDB]: https://thetvdb.com/
[Composer]: https://getcomposer.org
