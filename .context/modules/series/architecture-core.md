# Series - Architecture
_SOURCE: Public class signatures_
# Public class signatures
```
// Structure of documents
└── assets/
    └── classes/
        └── Series/
            └── Episode.php
            └── Season.php
            └── Series.php
            └── SeriesForm.php
            └── SeriesList.php

```
###  Path: `\assets\classes\Series/Episode.php`

```php
namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\OutputBuffering as OutputBuffering;
use Mistralys\SeriesManager\Manager as Manager;
use Mistralys\SeriesManager\Manager\Library as Library;
use Mistralys\SeriesManager\Manager\LibraryFile as LibraryFile;

class Episode
{
	public function getSeason(): Season
	{
		/* ... */
	}


	public function getNumber(): int
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getSynopsis(): string
	{
		/* ... */
	}


	/**
	 * @return array<int,array{label:string,url:string}>
	 */
	public function getSearchLinks(): array
	{
		/* ... */
	}


	public function isDownloaded(): bool
	{
		/* ... */
	}


	public function getDownloadStatusIcon(): string
	{
		/* ... */
	}


	public function isFoundOnDisk(): bool
	{
		/* ... */
	}


	public function getSeries(): Series
	{
		/* ... */
	}


	public function getSeasonNumber(): int
	{
		/* ... */
	}


	public function findInLibrary(): ?LibraryFile
	{
		/* ... */
	}


	public function getSearchString(): string
	{
		/* ... */
	}


	public function isComplete(): bool
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Series/Season.php`

```php
namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Mistralys\SeriesManager\Manager as Manager;

class Season
{
	public function getSeries(): Series
	{
		/* ... */
	}


	public function getNumber(): int
	{
		/* ... */
	}


	/**
	 * @return array<int,array{label:string,url:string}>
	 */
	public function getSearchLinks(): array
	{
		/* ... */
	}


	public function getSearchString(): string
	{
		/* ... */
	}


	public function getID(): int
	{
		/* ... */
	}


	/**
	 * @return Episode[]
	 */
	public function getEpisodes(): array
	{
		/* ... */
	}


	public function countEpisodes(): int
	{
		/* ... */
	}


	public function isComplete(): bool
	{
		/* ... */
	}


	public function getURLDelete(): string
	{
		/* ... */
	}


	public function delete(): void
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Series/Series.php`

```php
namespace Mistralys\SeriesManager\Series;

use Adrenth\Thetvdb\Client as Client;
use Adrenth\Thetvdb\Model\BasicEpisode as BasicEpisode;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\FileHelper\JSONFile as JSONFile;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\Request as Request;
use Mistralys\SeriesManager\Manager as Manager;
use Mistralys\SeriesManager\ManagerException as ManagerException;
use Throwable as Throwable;

class Series
{
	public const KEY_TVDB_ALIAS = 'tvdbAlias';
	public const KEY_TVDB_ID = 'tvdbID';
	public const KEY_IMDB_ID = 'imdbID';
	public const KEY_NAME = 'name';
	public const KEY_ARCHIVED = 'archived';
	public const INFO_STATUS = 'status';
	public const INFO_GENRE = 'genre';
	public const INFO_NETWORK = 'network';
	public const INFO_OVERVIEW = 'overview';
	public const INFO_SEASON = 'season';
	public const INFO_FIRST_AIRED = 'firstAired';
	public const INFO_SITE_RATING = 'siteRating';
	public const INFO_SITE_RATING_COUNT = 'siteRatingCount';
	public const KEY_INFO = 'stored-info';
	public const INFO_SEASONS = 'seasons';
	public const INFO_SEASON_ID = 'id';
	public const INFO_SEASON_EPISODES = 'episodes';
	public const INFO_EPISODE_ID = 'id';
	public const INFO_EPISODE_NAME = 'name';
	public const INFO_EPISODE_OVERVIEW = 'overview';
	const KEY_FAVORITE = 'favorite';
	public const EDIT_TAB_SUMMARY = 'summary';
	public const EDIT_TAB_SEASONS = 'seasons';
	public const EDIT_TAB_SETTINGS = 'settings';

	public function getName(): string
	{
		/* ... */
	}


	public function isArchived(): bool
	{
		/* ... */
	}


	public function getStatus(): string
	{
		/* ... */
	}


	public function getTVDBAlias(): string
	{
		/* ... */
	}


	public function getTVDBID()
	{
		/* ... */
	}


	public function getTVDBLink(): ?string
	{
		/* ... */
	}


	public function getIMDBID(): string
	{
		/* ... */
	}


	public function getRarbgLink(): ?string
	{
		/* ... */
	}


	public function getIMDBLink(): ?string
	{
		/* ... */
	}


	public function getLastDLSeason(): int
	{
		/* ... */
	}


	public function getLastDLEpisode(): int
	{
		/* ... */
	}


	public function setLastDLSeason($season)
	{
		/* ... */
	}


	public function setLastDLEpisode($episode)
	{
		/* ... */
	}


	public function toArray(): array
	{
		/* ... */
	}


	public function fetchData(Client $client, bool $clearCache = true, bool $dump = false): void
	{
		/* ... */
	}


	/**
	 * @return Season[]
	 */
	public function getSeasons(): array
	{
		/* ... */
	}


	/**
	 * @return array<int,array{url:string,label:string}>
	 */
	public function getLinks(): array
	{
		/* ... */
	}


	public function setName(string $name): bool
	{
		/* ... */
	}


	public function setArchived(bool $archived): self
	{
		/* ... */
	}


	public function save(): self
	{
		/* ... */
	}


	public function setTVDBAlias(string $id): bool
	{
		/* ... */
	}


	public function setIMDBID(string $id): bool
	{
		/* ... */
	}


	public function setTVDBID(string $id): bool
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getMessages(): array
	{
		/* ... */
	}


	public function countSeasons(): int
	{
		/* ... */
	}


	public function countEpisodes(): int
	{
		/* ... */
	}


	public function getSynopsis(): string
	{
		/* ... */
	}


	public function hasInfo(): bool
	{
		/* ... */
	}


	/**
	 * @param string $key
	 * @return string|number|bool|array|NULL
	 */
	public function getInfo(string $key)
	{
		/* ... */
	}


	public function getURLEdit(array $params = []): string
	{
		/* ... */
	}


	public function getURLEditTab(string $tabID, array $params = []): string
	{
		/* ... */
	}


	public function getURLSeasons(array $params = []): string
	{
		/* ... */
	}


	public function getURLDelete(array $params = []): string
	{
		/* ... */
	}


	public function getURLArchive(?array $params = null, ?string $returnPage = null): string
	{
		/* ... */
	}


	public function getURLUnarchive(?array $params = null, ?string $returnPage = null): string
	{
		/* ... */
	}


	public function getURLFavorite(?array $params = null, ?string $returnPage = null): string
	{
		/* ... */
	}


	public function getURLUnfavorite(?array $params = null, ?string $returnPage = null): string
	{
		/* ... */
	}


	public function getURLFetch(array $params = []): string
	{
		/* ... */
	}


	public function getURLClearAndFetch(array $params = []): string
	{
		/* ... */
	}


	public function getCurrentSeason(): int
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getGenres(): array
	{
		/* ... */
	}


	public function isComplete(): bool
	{
		/* ... */
	}


	public function isFavorite(): bool
	{
		/* ... */
	}


	public function setFavorite(bool $favorite): self
	{
		/* ... */
	}


	public function renderFavoriteIcon(): string
	{
		/* ... */
	}


	/**
	 * @return never
	 */
	public function redirectToReturnPage(): void
	{
		/* ... */
	}


	public function getSearchText(): string
	{
		/* ... */
	}


	public function getSeasonByRequest(): ?Season
	{
		/* ... */
	}


	public function seasonExists(int $id): bool
	{
		/* ... */
	}


	public function getSeasonByID(int $id): Season
	{
		/* ... */
	}


	public function deleteSeason(Season $season): self
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Series/SeriesForm.php`

```php
namespace Mistralys\SeriesManager\Series;

use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\Request as Request;
use Mistralys\SeriesManager\Manager as Manager;
use Mistralys\SeriesManager\SeriesCollection as SeriesCollection;

class SeriesForm
{
	public const SETTING_IMDB_ID = 'imdbid';
	public const SETTING_TVDB_ALIAS = 'tvdbalias';
	public const SETTING_TVDB_ID = 'tvdbid';
	public const SETTING_NAME = 'name';
	public const REQUEST_PARAM_NAME_SEARCH = 'name-search';

	public function display(): void
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function setTitleEnabled(bool $enabled): self
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Series/SeriesList.php`

```php
namespace Mistralys\SeriesManager\Series;

use AppUtils\JSHelper as JSHelper;
use AppUtils\OutputBuffering as OutputBuffering;
use AppUtils\Request as Request;
use Mistralys\SeriesManager\Manager as Manager;
use Mistralys\SeriesManager\SeriesCollection as SeriesCollection;
use Mistralys\SeriesManager\UI as UI;

class SeriesList
{
	/**
	 * @return Series[]
	 */
	public function getSeries(): array
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function selectArchived(bool $archived): self
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 9.16 KB
- **Lines**: 657
File: `modules/series/architecture-core.md`
