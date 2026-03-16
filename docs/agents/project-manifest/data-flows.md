# Key Data Flows

## 1. Application Boot

```
index.php
  └─ require vendor/autoload.php             (Composer autoloader)
  └─ require config-local.php               (defines APP_PASSWORD, APP_API_KEY, etc.)
  └─ Manager::getInstance()->start()
       ├─ Manager::initLocalization()        (registers locales + source folder)
       ├─ selectLocale()                     (reads $_REQUEST['selectLocale'] or $_SESSION['locale'])
       ├─ checkDarkModeToggle()              (reads 'toggleDarkMode' GET param → sets $_SESSION)
       ├─ checkLogin()                       (validates $_SESSION['auth'] against encoded APP_PASSWORD)
       │    └─ if not logged in → page = 'login'
       ├─ resolves page from $_REQUEST['page']
       ├─ ob_start()
       │    └─ require pages/{page}.php      (page content rendered to buffer)
       ├─ ob_get_clean()                     (content stored in Manager::$content)
       └─ require pages/_frame.php           (outputs full HTML shell, injects Manager::getContent())
```

---

## 2. Add a New Series

```
User submits Add form (POST)
  └─ pages/add.php
       └─ new SeriesForm(null)
            ├─ loadDefaults()               (pre-fills 'name-search' from GET if present)
            └─ handle()
                 ├─ reads $_REQUEST fields: name, imdbid, tvdbid, tvdbalias
                 ├─ SeriesCollection::add(array $data)  → creates new Series object
                 ├─ SeriesCollection::save()            → writes data/series.json
                 └─ redirect to Series::getURLEditTab(EDIT_TAB_SETTINGS)
```

---

## 3. Edit Series Settings

```
User opens edit page (GET ?page=edit&id={imdbID})
  └─ pages/edit.php
       ├─ Manager::getSelected()            (finds Series by IMDB ID from $_REQUEST['id'])
       └─ require pages/edit/tab-settings.php
            └─ new SeriesForm($series)
                 ├─ loadDefaults()          (pre-fills from Series properties)
                 └─ on POST save=yes:
                      ├─ Series::setName(), setTVDBAlias(), setIMDBID(), setTVDBID()
                      ├─ SeriesCollection::save()
                      └─ redirect to Series::getURLEditTab(EDIT_TAB_SETTINGS)
```

---

## 4. Fetch Series Data from TheTVDB (Single Series)

```
User clicks "Fetch data" in edit view (GET ?page=edit&id={imdbID}&fetch=yes)
  └─ pages/edit.php
       ├─ $request->getBool('fetch') → true
       ├─ Manager::createClient()            (authenticates with TheTVDB API using APP_API_KEY + APP_SUBSCRIBER_PIN)
       ├─ Series::fetchData($client, $clearCache)
       │    ├─ checks cache/  {imdbID}-info.json
       │    │    └─ if exists and clearCache=false → loads from JSON, returns
       │    ├─ $client->series()->get(tvdbID)   (fetches series metadata)
       │    ├─ loops pages of $client->series()->getEpisodes(tvdbID, $page)
       │    │    └─ builds $info array: status, genre, network, overview, seasons, episodes
       │    ├─ writes cache/{imdbID}-info.json
       │    └─ stores result in Series::$data[KEY_INFO]
       ├─ SeriesCollection::save()            (persists updated series data)
       └─ redirect to Series::getURLEditTab(EDIT_TAB_SEASONS)
```

---

## 5. Fetch Data for All Series (Bulk)

```
User clicks "Fetch now" on fetch page (POST ?page=fetch&confirm=yes)
  └─ pages/fetch.php
       ├─ reads 'clear_cache' checkbox
       └─ SeriesCollection::fetchData($clearCache)
            └─ foreach Series → Series::fetchData($client, $clearCache)
                 (same flow as §4 above, per series)
```

---

## 6. Series List — Track Watching Progress

```
User submits "Last watched" update (POST ?page=list&update=1)
  └─ pages/list.php
       └─ new SeriesList()
            └─ handleActions()
                 ├─ reads $_REQUEST['series'] as array: imdbID → {lastDLSeason, lastDLEpisode}
                 ├─ for each entry: Series::setLastDLSeason() + Series::setLastDLEpisode()
                 ├─ SeriesCollection::save()
                 └─ redirect back to current page
```

---

## 7. Archive / Unarchive / Favorite / Unfavorite

```
User clicks action link (GET ?page=archive&id={imdbID}&returnPage=list)
  └─ pages/archive.php (or unarchive.php / favorite.php / unfavorite.php)
       ├─ Manager::getSelected()             (resolves Series from 'id' param)
       ├─ Series::setArchived(true)          (or setFavorite, etc.)
       ├─ Series::save()                     → SeriesCollection::save()
       └─ Series::redirectToReturnPage()     → Manager::redirectToReturnPage()
            └─ reads 'returnPage' param → redirects to ?page={returnPage}&id={imdbID}
```

---

## 8. Delete a Series

```
User confirms delete (GET ?page=delete&id={imdbID})
  └─ pages/delete.php
       ├─ Manager::getSelected()
       ├─ SeriesCollection::delete($selected)   (removes from in-memory array)
       ├─ SeriesCollection::save()              (rewrites data/series.json without the deleted series)
       └─ redirect to ./
```

---

## 9. Library Index Creation

```
User clicks "Build/Refresh index" (GET ?page=library&tab=index-status&create-index=yes)
  └─ pages/library/index-status.php
       └─ Library::createFromConfig()       (singleton using APP_LIBRARY_PATHS)
            └─ Library::createIndex()
                 ├─ for each configured path in APP_LIBRARY_PATHS:
                 │    ├─ FileHelper::createFileFinder(path)
                 │    │    └─ scans recursively for video extensions (mov, mp4, mkv, avi, …)
                 │    ├─ for each file → Library::indexFile()
                 │    │    ├─ Library::parseName(filename)
                 │    │    │    ├─ Library::normalizeName()   (lowercases, strips punctuation)
                 │    │    │    ├─ Library::detectEpisodeNumbers()  (regex S##E## pattern)
                 │    │    │    └─ Library::getNameAlias()    (applies data/name-aliases.json)
                 │    │    └─ creates LibraryFile (name, season, episode) → registers in seasonIndex
                 │    └─ for each subfolder → LibrarySubfolder registered
                 └─ Library::writeCache()    → writes cache/library.json
       └─ redirect to library page
```

---

## 10. Episode Disk Detection

```
Episode::isFoundOnDisk()
  └─ Episode::findInLibrary()
       └─ Library::createFromConfig()->findEpisode(seriesName, seasonNr, episodeNr)
            ├─ Library::load()              (loads cache/library.json if not already loaded)
            ├─ Library::normalizeName(seriesName)
            ├─ looks up $seasonIndex[seasonNr][episodeNr]
            └─ returns first LibraryFile whose name matches the normalized series name, or null
```

---

## 11. Authentication

```
Any page request (unauthenticated)
  └─ Manager::start()
       └─ checkLogin()
            ├─ $_SESSION['auth'] not set → returns false → page = 'login'
            └─ $_SESSION['auth'] === sha1(APP_PASSWORD . '-' . APP_SALT) → logged in

Login form submission (POST ?page=login)
  └─ pages/login.php
       ├─ FormHandler validates password field using Manager::isPasswordValid()
       ├─ if valid: $_SESSION['auth'] = Manager::encodePassword($values['password'])
       └─ redirect to ?page=list

Logout (GET ?page=login&logout=yes)
  └─ pages/login.php
       ├─ unset($_SESSION['auth'])
       └─ redirect to ?page=login
```

