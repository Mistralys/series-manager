# Public API Surface

Namespace root: `Mistralys\SeriesManager`  
All classes use `declare(strict_types=1)`.

---

## `Manager` (Singleton)

**File:** `assets/classes/Manager.php`  
**Namespace:** `Mistralys\SeriesManager`

### Constants

```php
const REQUEST_PARAM_PAGE = 'page';
const SESSION_DARK_MODE  = 'darkmode';
const REQUEST_VAR_RETURN_PAGE = 'returnPage';
```

### Static Methods

```php
public static function getInstance() : Manager
public static function isDarkMode() : bool
public static function getDocumentTitle() : string
public static function setDocumentTitle(?string $title) : void
public static function getName() : string
public static function initLocalization() : void
```

### Instance Methods

```php
public function start() : void
public function getVersion() : string
public function getPages() : array                          // returns array<string,string> of pageID => label
public function getPageID() : string
public function getContent() : string
public function getSeries() : SeriesCollection
public function getSelectedID() : string                    // from $_REQUEST['id']; empty string if none
public function getSelected() : ?Series
public function isLoggedIn() : bool
public function isPasswordValid(string $password) : bool
public function encodePassword(string $password) : string   // SHA-1 hash with salt
public function createClient() : \CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI    // creates/caches TheTVDB API client
public function getCustomLinkDefs() : array                 // returns array<int,array{label:string,template:string}>
public function prepareCustomLinks(string $searchTerm) : array  // returns array<int,array{label:string,url:string}>
public function prepareLinks(array $linkDefs, string $searchTerm) : array
public function getURLAdd(array $params = []) : string
public function getURL(array $params = []) : string
public function redirectToReturnPage(array $params = []) : never
public function setDarkMode(bool $enabled) : self
public function redirect(string $url) : never
```

---

## `SeriesCollection`

**File:** `assets/classes/SeriesCollection.php`  
**Namespace:** `Mistralys\SeriesManager`

```php
public function __construct()
public function add(array $data) : Series
public function getAll() : array                // returns Series[], sorted by name (natural case-insensitive)
public function getByIMDBID(string $id) : ?Series
public function IMDBIDExists(string $id) : bool
public function delete(Series $targetSeries) : void
public function save() : void                   // writes to data/series.json
public function fetchData(bool $clearCache = false) : array  // returns string[] of messages
```

---

## `Series\Series`

**File:** `assets/classes/Series/Series.php`  
**Namespace:** `Mistralys\SeriesManager\Series`

### Constants

```php
// Data keys stored in data/series.json
const KEY_TVDB_ALIAS = 'tvdbAlias';
const KEY_TVDB_ID    = 'tvdbID';
const KEY_IMDB_ID    = 'imdbID';
const KEY_NAME       = 'name';
const KEY_ARCHIVED   = 'archived';
const KEY_FAVORITE   = 'favorite';
const KEY_INFO       = 'stored-info';  // sub-key holding fetched TVDB data

// Keys within the stored-info blob
const INFO_STATUS          = 'status';
const INFO_GENRE           = 'genre';
const INFO_NETWORK         = 'network';
const INFO_OVERVIEW        = 'overview';
const INFO_SEASON          = 'season';
const INFO_FIRST_AIRED     = 'firstAired';
const INFO_SITE_RATING     = 'siteRating';
const INFO_SITE_RATING_COUNT = 'siteRatingCount';
const INFO_SEASONS         = 'seasons';
const INFO_SEASON_ID       = 'id';
const INFO_SEASON_EPISODES = 'episodes';
const INFO_EPISODE_ID      = 'id';
const INFO_EPISODE_NAME    = 'name';
const INFO_EPISODE_OVERVIEW = 'overview';

// Edit tab identifiers
const EDIT_TAB_SUMMARY  = 'summary';
const EDIT_TAB_SEASONS  = 'seasons';
const EDIT_TAB_SETTINGS = 'settings';
```

### Constructor

```php
public function __construct(array $data)
```

### Getters

```php
public function getName() : string
public function getIMDBID() : string
public function getTVDBID() : mixed
public function getTVDBAlias() : string
public function getTVDBLink() : ?string
public function getIMDBLink() : ?string
public function getRarbgLink() : ?string
public function getLastDLSeason() : int
public function getLastDLEpisode() : int
public function getStatus() : string
public function getSynopsis() : string
public function getCurrentSeason() : int
public function getGenres() : array                     // returns string[]
public function getSeasons() : array                    // returns Season[]
public function countSeasons() : int
public function countEpisodes() : int
public function hasInfo() : bool
public function getInfo(string $key) : string|int|float|bool|array|null
public function getMessages() : array                   // returns string[]
public function getLinks() : array                      // returns array<int,array{url:string,label:string}>
public function getSearchText() : string                // name + TVDB ID + IMDB ID concatenated
public function isFavorite() : bool
public function isArchived() : bool
public function isComplete() : bool                     // true if all seasons/episodes found on disk
public function getSeasonByRequest() : ?Season
public function seasonExists(int $id) : bool
public function getSeasonByID(int $id) : Season         // throws ManagerException if not found
```

### Setters & Actions

```php
public function setName(string $name) : bool
public function setIMDBID(string $id) : bool
public function setTVDBID(string $id) : bool
public function setTVDBAlias(string $id) : bool
public function setLastDLSeason(mixed $season) : bool|false
public function setLastDLEpisode(mixed $episode) : bool|false
public function setArchived(bool $archived) : self
public function setFavorite(bool $favorite) : self
public function save() : self                           // delegates to SeriesCollection::save()
public function deleteSeason(Season $season) : self
public function fetchData(\CanIHaveSomeCoffee\TheTVDbAPI\TheTVDbAPI $client, bool $clearCache = true, bool $dump = false) : void
public function redirectToReturnPage() : never
```

### URL Generators

```php
public function getURLEdit(array $params = []) : string
public function getURLEditTab(string $tabID, array $params = []) : string
public function getURLSeasons(array $params = []) : string
public function getURLDelete(array $params = []) : string
public function getURLArchive(?array $params = null, ?string $returnPage = null) : string
public function getURLUnarchive(?array $params = null, ?string $returnPage = null) : string
public function getURLFavorite(?array $params = null, ?string $returnPage = null) : string
public function getURLUnfavorite(?array $params = null, ?string $returnPage = null) : string
public function getURLFetch(array $params = []) : string
public function getURLClearAndFetch(array $params = []) : string
```

### Rendering

```php
public function renderFavoriteIcon() : string
public function toArray() : array
```

---

## `Series\Season`

**File:** `assets/classes/Series/Season.php`  
**Namespace:** `Mistralys\SeriesManager\Series`

```php
public function __construct(Series $series, int $number, array $data)
public function getSeries() : Series
public function getNumber() : int
public function getID() : int                       // TheTVDB season ID
public function getEpisodes() : array               // returns Episode[]
public function countEpisodes() : int
public function isComplete() : bool                 // true if all episodes found on disk
public function getSearchString() : string          // e.g. "Game of Thrones s01"
public function getSearchLinks() : array            // returns array<int,array{label:string,url:string}>
public function getURLDelete() : string
public function delete() : void                     // removes season from parent Series
```

---

## `Series\Episode`

**File:** `assets/classes/Series/Episode.php`  
**Namespace:** `Mistralys\SeriesManager\Series`

```php
public function __construct(Season $season, int $number, array $data)
public function getSeason() : Season
public function getSeries() : Series
public function getNumber() : int
public function getID() : int                   // TheTVDB episode ID
public function getName() : string
public function getSynopsis() : string
public function getSeasonNumber() : int
public function isDownloaded() : bool           // based on lastDLSeason/lastDLEpisode stored values
public function isFoundOnDisk() : bool          // based on Library index
public function isComplete() : bool             // alias for isFoundOnDisk()
public function findInLibrary() : ?LibraryFile
public function getSearchString() : string      // e.g. "Game of Thrones s01e05"
public function getSearchLinks() : array        // returns array<int,array{label:string,url:string}>
public function getDownloadStatusIcon() : string
```

---

## `Manager\Library`

**File:** `assets/classes/Manager/Library.php`  
**Namespace:** `Mistralys\SeriesManager\Manager`

### Constants

```php
const REQUEST_VAR_TAB   = 'tab';
const TAB_INDEX_STATUS  = 'index-status';
const TAB_NAME_ALIASES  = 'name-aliases';
const TAB_FILES_LIST    = 'files-list';
const TAB_FOLDERS_LIST  = 'folders-list';
const DEFAULT_TAB       = self::TAB_INDEX_STATUS;
const KEY_FILES         = 'files';
const KEY_FOLDERS       = 'folders';
```

### Constructor

```php
public function __construct(array $paths)   // accepts string|FolderInfo|SplFileInfo paths
```

### Static Factory

```php
public static function createFromConfig() : Library   // uses APP_LIBRARY_PATHS constant
```

### Index Management

```php
public function createIndex() : void                  // scans disk paths, writes cache
public function clearCache() : void
public function cacheExists() : bool
public function getCacheFile() : \AppUtils\FileHelper\JSONFile
```

### Querying

```php
public function getFiles() : array                    // returns LibraryFile[] from cache
public function getSeriesNames() : array              // returns string[] of distinct series names
public function getAvailableFolders(string $sortBy = 'name', string $sortDir = 'asc') : array  // returns LibrarySubfolder[]
public function findEpisode(string $seriesName, int $seasonNr, int $episodeNr) : ?LibraryFile
```

### Name Aliases

```php
public function getNameAliases() : array              // returns array<string,string>
public function getNameAlias(string $name) : string
public function setNameAlias(string $name, string $alias) : self
public function deleteNameAlias(string $name) : self
```

### URL Generators

```php
public function getURL(array $params = []) : string
public function getURLCreateIndex(array $params = []) : string
public function getURLClearCache(array $params = []) : string
public function getURLFilesList(array $params = []) : string
public function getURLNameAliases(array $params = []) : string
```

### Static Utilities

```php
public static function normalizeName(string $name) : string
public static function removeExtraneousSpaces(string $subject) : string
public static function detectEpisodeNumbers(string $subject) : array
public static function detectYears(string $subject) : array
public function parseName(string $name) : ?array      // returns array{name:string,season:int,episode:int}|null
```

---

## `Manager\LibraryFile`

**File:** `assets/classes/Manager/LibraryFile.php`  
**Namespace:** `Mistralys\SeriesManager\Manager`

### Constants

```php
const ERROR_INVALID_DATA_ARRAY = 1389011;
const KEY_FILE_PATH = 'p';
const KEY_NAME      = 'n';
const KEY_SEASON    = 's';
const KEY_EPISODE   = 'e';
```

### Constructor & Factory

```php
public function __construct(\AppUtils\FileHelper\FileInfo $file, string $name, int $season, int $episode)
public static function createFromArray(array $data) : LibraryFile   // throws LibraryException on invalid data
```

### Methods

```php
public function getFile() : \AppUtils\FileHelper\FileInfo
public function getName() : string
public function getSeason() : int
public function getEpisode() : int
public function getEpisodeName() : string       // e.g. "S01E05"
public function getNameWithEpisode() : string   // e.g. "Game of Thrones S01E05"
public function toArray() : array               // compact serialization for cache
```

---

## `Manager\LibrarySubfolder`

**File:** `assets/classes/Manager/LibrarySubfolder.php`  
**Namespace:** `Mistralys\SeriesManager\Manager`

### Constants

```php
const KEY_PATH           = 'path';
const KEY_NAME           = 'name';
const KEY_DATE           = 'date';
const KEY_LIBRARY_FOLDER = 'libraryFolderName';
```

### Constructor & Factory

```php
public function __construct(string $libraryFolderName, \AppUtils\FileHelper\FolderInfo $folder, string $name, \DateTime $date)
public static function createFromArray(array $def) : ?LibrarySubfolder  // returns null if path no longer exists
```

### Methods

```php
public function getPath() : \AppUtils\FileHelper\FolderInfo
public function getName() : string
public function getNameLinked() : string        // name wrapped in <a> if a matching Series is found
public function getLibraryFolderName() : string
public function getDate() : \DateTime
public function exists() : bool                 // true if a matching Series is found in the collection
public function detectSeries() : ?Series
public function getURLAdd(array $params = []) : string
public function toArray() : array
```

---

## `Series\SeriesList`

**File:** `assets/classes/Series/SeriesList.php`  
**Namespace:** `Mistralys\SeriesManager\Series`

```php
public function __construct()
public function selectArchived(bool $archived) : self
public function getSeries() : array             // returns Series[] filtered by archived flag
public function getTitle() : string
public function render() : string               // renders full HTML table
public function display() : void                // echo render()
```

---

## `Series\SeriesForm`

**File:** `assets/classes/Series/SeriesForm.php`  
**Namespace:** `Mistralys\SeriesManager\Series`

### Constants

```php
const SETTING_IMDB_ID          = 'imdbid';
const SETTING_TVDB_ALIAS       = 'tvdbalias';
const SETTING_TVDB_ID          = 'tvdbid';
const SETTING_NAME             = 'name';
const REQUEST_PARAM_NAME_SEARCH = 'name-search';
```

### Constructor

```php
public function __construct(?Series $series)  // null = add mode, Series = edit mode
```

### Methods

```php
public function render() : string
public function display() : void    // echo render()
```

---

## `FormHandler`

**File:** `assets/classes/FormHandler.php`  
**Namespace:** `Mistralys\SeriesManager`

```php
const RENDERER_ID = 'Bootstrap3';

public function __construct(string $id)
public function getForm() : \HTML_QuickForm2
public function isValid() : bool
public function getValues() : array
public function render() : string
public function display() : void
public function addHiddenVar(string $name, string $value) : self
```

---

## `Bootstrap3Renderer`

**File:** `assets/classes/Bootstrap3Renderer.php`  
**Namespace:** `Mistralys\SeriesManager`  
**Extends:** `HTML_QuickForm2_Renderer`

```php
const RENDERER_ID = 'Bootstrap3Renderer';

public function renderHidden(\HTML_QuickForm2_Node $element) : void
public function renderElement(\HTML_QuickForm2_Node $element) : void
public function startGroup(\HTML_QuickForm2_Container_Group $group) : void
public function finishGroup(\HTML_QuickForm2_Container_Group $group) : void
public function startForm(\HTML_QuickForm2_Node $form) : void
public function finishForm(\HTML_QuickForm2_Node $form) : void
public function startContainer(\HTML_QuickForm2_Node $container) : void
public function finishContainer(\HTML_QuickForm2_Node $container) : void
public function getID() : string
public function reset() : self
public function __toString() : string
```

---

## `UI`

**File:** `assets/classes/UI.php`  
**Namespace:** `Mistralys\SeriesManager`

```php
public static function prettyBool(bool $value) : string  // returns Bootstrap 3 glyphicon HTML
```

---

## `ManagerException`

**File:** `assets/classes/ManagerException.php`  
**Namespace:** `Mistralys\SeriesManager`  
**Extends:** `\AppUtils\BaseException`

```php
const ERROR_UNKNOWN_SEASON = 180601;
```

---

## `Manager\LibraryException`

**File:** `assets/classes/Manager/LibraryException.php`  
**Namespace:** `Mistralys\SeriesManager\Manager`  
**Extends:** `\AppUtils\BaseException` (assumed, mirrors pattern of `ManagerException`)

