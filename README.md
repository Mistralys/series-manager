# Series Manager

PHP web tool that keeps track of TV series watched and downloaded.

## Setup

1. Run `composer install`.
2. Copy `config-local.dist.php` to `config-local.php`.
3. Edit the configuration settings in the file.
4. Optional: Add custom search links (see below).
5. Open the project folder in a browser via the webserver.

## Adding custom search links

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
