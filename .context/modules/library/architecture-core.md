# Library - Architecture
_SOURCE: Public class signatures_
# Public class signatures
```
// Structure of documents
└── assets/
    └── classes/
        └── Manager/
            └── Library.php
            └── LibraryException.php
            └── LibraryFile.php
            └── LibrarySubfolder.php

```
###  Path: `\assets\classes\Manager/Library.php`

```php
namespace Mistralys\SeriesManager\Manager;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Mistralys\SeriesManager\Manager as Manager;
use SplFileInfo as SplFileInfo;

class Library
{
	public const REQUEST_VAR_TAB = 'tab';
	public const TAB_INDEX_STATUS = 'index-status';
	public const TAB_NAME_ALIASES = 'name-aliases';
	public const TAB_FILES_LIST = 'files-list';
	public const TAB_FOLDERS_LIST = 'folders-list';
	public const DEFAULT_TAB = self::TAB_INDEX_STATUS;
	public const KEY_FILES = 'files';
	public const KEY_FOLDERS = 'folders';

	public function getCacheFile(): JSONFile
	{
		/* ... */
	}


	public function clearCache(): void
	{
		/* ... */
	}


	public function getURLFilesList(array $params = []): string
	{
		/* ... */
	}


	public function getURLCreateIndex(array $params = []): string
	{
		/* ... */
	}


	public function getURLClearCache(array $params = []): string
	{
		/* ... */
	}


	public function getURL(array $params = []): string
	{
		/* ... */
	}


	public function getURLNameAliases(array $params = []): string
	{
		/* ... */
	}


	/**
	 * Creates a library instance using the library
	 * paths configured in the main configuration file.
	 *
	 * @return Library
	 */
	public static function createFromConfig(): Library
	{
		/* ... */
	}


	public function createIndex(): void
	{
		/* ... */
	}


	public function findEpisode(string $seriesName, int $seasonNr, int $episodeNr): ?LibraryFile
	{
		/* ... */
	}


	/**
	 * @return LibraryFile[]
	 */
	public function getFiles(): array
	{
		/* ... */
	}


	public static function normalizeName(string $name): string
	{
		/* ... */
	}


	public static function removeExtraneousSpaces(string $subject): string
	{
		/* ... */
	}


	public static function detectEpisodeNumbers(string $subject): array
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return array{name:string,season:int,episode:int}|null
	 */
	public function parseName(string $name): ?array
	{
		/* ... */
	}


	/**
	 * @param string $subject
	 * @return int[]
	 */
	public static function detectYears(string $subject): array
	{
		/* ... */
	}


	public function getNameAlias(string $name): string
	{
		/* ... */
	}


	/**
	 * @return array<string,string>
	 */
	public function getNameAliases(): array
	{
		/* ... */
	}


	public function setNameAlias(string $name, string $alias): self
	{
		/* ... */
	}


	public function cacheExists(): bool
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getSeriesNames(): array
	{
		/* ... */
	}


	public function deleteNameAlias(string $name): self
	{
		/* ... */
	}


	/**
	 * @return LibrarySubfolder[]
	 */
	public function getAvailableFolders(string $sortBy = 'name', string $sortDir = 'asc'): array
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Manager/LibraryException.php`

```php
namespace Mistralys\SeriesManager\Manager;

use Mistralys\SeriesManager\ManagerException as ManagerException;

class LibraryException extends ManagerException
{
}


```
###  Path: `\assets\classes\Manager/LibraryFile.php`

```php
namespace Mistralys\SeriesManager\Manager;

use AppUtils\FileHelper\FileInfo as FileInfo;
use Mistralys\SeriesManager\ManagerException as ManagerException;

class LibraryFile
{
	public const ERROR_INVALID_DATA_ARRAY = 1389011;
	public const KEY_FILE_PATH = 'p';
	public const KEY_NAME = 'n';
	public const KEY_SEASON = 's';
	public const KEY_EPISODE = 'e';

	public function getFile(): FileInfo
	{
		/* ... */
	}


	public function getSeason(): int
	{
		/* ... */
	}


	public function getEpisode(): int
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getEpisodeName(): string
	{
		/* ... */
	}


	public function getNameWithEpisode(): string
	{
		/* ... */
	}


	/**
	 * Serializes the file's data to an array, meant to
	 * be compact (keys are very short).
	 *
	 * @return array<string,string|int>
	 */
	public function toArray(): array
	{
		/* ... */
	}


	public static function createFromArray(array $data): LibraryFile
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes\Manager/LibrarySubfolder.php`

```php
namespace Mistralys\SeriesManager\Manager;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use AppUtils\Microtime as Microtime;
use DateTime as DateTime;
use Mistralys\SeriesManager\Manager as Manager;
use Mistralys\SeriesManager\ManagerException as ManagerException;
use Mistralys\SeriesManager\Series\Series as Series;
use Mistralys\SeriesManager\Series\SeriesForm as SeriesForm;
use Mistralys\SeriesManager\UI as UI;

class LibrarySubfolder
{
	public const KEY_PATH = 'path';
	public const KEY_NAME = 'name';
	public const KEY_DATE = 'date';
	public const KEY_LIBRARY_FOLDER = 'libraryFolderName';

	public function getPath(): FolderInfo
	{
		/* ... */
	}


	public function getLibraryFolderName(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getNameLinked(): string
	{
		/* ... */
	}


	public function getURLAdd(array $params = []): string
	{
		/* ... */
	}


	public function getDate(): DateTime
	{
		/* ... */
	}


	public function exists(): bool
	{
		/* ... */
	}


	public function detectSeries(): ?Series
	{
		/* ... */
	}


	public function toArray(): array
	{
		/* ... */
	}


	public static function createFromArray(array $def): ?LibrarySubfolder
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.04 KB
- **Lines**: 381
File: `modules/library/architecture-core.md`
