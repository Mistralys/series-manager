<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Manager;

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use Mistralys\SeriesManager\Manager;
use SplFileInfo;
use const Mistralys\SeriesManager\APP_LIBRARY_PATHS;

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

    /**
     * @var FolderInfo[]
     */
    private array $paths = array();

    /**
     * @var string[]
     */
    private array $extensions = array(
        'mov',
        'mpg',
        'mpeg',
        'mp4',
        'avi',
        'qt',
        'mkv'
    );
    private JSONFile $cacheFile;
    private JSONFile $aliasesFile;

    /**
     * @param array<int,string|FolderInfo|SplFileInfo> $paths
     */
    public function __construct(array $paths)
    {
        $this->cacheFile = JSONFile::factory(__DIR__.'/../../../cache/library.json');
        $this->aliasesFile = JSONFile::factory(__DIR__.'/../../../data/name-aliases.json');

        foreach($paths as $path)
        {
            $this->paths[] = FolderInfo::factory($path);
        }
    }

    public function getCacheFile() : JSONFile
    {
        return $this->cacheFile;
    }

    public function clearCache() : void
    {
        $this->cacheFile->delete();
    }

    public function getURLFilesList(array $params=array()) : string
    {
        $params[self::REQUEST_VAR_TAB] = self::TAB_FILES_LIST;

        return $this->getURL($params);
    }

    public function getURLCreateIndex(array $params=array()) : string
    {
        $params[self::REQUEST_VAR_TAB] = self::TAB_INDEX_STATUS;
        $params['create-index'] = 'yes';

        return $this->getURL($params);
    }

    public function getURLClearCache(array $params=array()) : string
    {
        $params[self::REQUEST_VAR_TAB] = self::TAB_INDEX_STATUS;
        $params['clear-cache'] = 'yes';

        return $this->getURL($params);
    }

    public function getURL(array $params=array()) : string
    {
        $params[Manager::REQUEST_PARAM_PAGE] = 'library';

        return Manager::getInstance()->getURL($params);
    }

    public function getURLNameAliases(array $params=array()) : string
    {
        $params[self::REQUEST_VAR_TAB] = self::TAB_NAME_ALIASES;

        return $this->getURL($params);
    }

    private static ?Library $configInstance = null;

    /**
     * Creates a library instance using the library
     * paths configured in the main configuration file.
     *
     * @return Library
     */
    public static function createFromConfig() : Library
    {
        if(!isset(self::$configInstance))
        {
            self::$configInstance = new Library(APP_LIBRARY_PATHS);
        }

        return self::$configInstance;
    }

    /**
     * @return LibraryFile[]
     */
    private function load() : array
    {
        if(isset($this->files)) {
            return $this->files;
        }

        $this->files = array();

        $this->loadCache();

        return $this->files;
    }

    private function loadCache() : void
    {
        if(!$this->cacheFile->exists()) {
            return;
        }

        $data = $this->cacheFile->parse();

        if(isset($data[self::KEY_FILES]))
        {
            foreach ($data[self::KEY_FILES] as $def)
            {
                $this->registerFile(LibraryFile::createFromArray($def));
            }
        }

        if(isset($data[self::KEY_FOLDERS]))
        {
            foreach ($data[self::KEY_FOLDERS] as $def)
            {
                $this->registerFolder(LibrarySubfolder::createFromArray($def));
            }
        }

        return;
    }

    public function createIndex() : void
    {
        $this->files = array();
        $this->folders = array();

        foreach($this->paths as $path)
        {
            if($path->exists()) {
                $this->indexPath($path);
            }
        }

        $this->writeCache();
    }

    private function writeCache() : void
    {
        $data = array(
            self::KEY_FILES => array(),
            self::KEY_FOLDERS => array()
        );

        foreach($this->files as $file)
        {
            $data[self::KEY_FILES][] = $file->toArray();
        }

        foreach ($this->folders as $folder)
        {
            $data[self::KEY_FOLDERS][] = $folder->toArray();
        }

        $this->cacheFile->putData($data, false);
    }

    private function indexPath(FolderInfo $path) : void
    {
        $files = FileHelper::createFileFinder($path)
            ->includeExtensions($this->extensions)
            ->makeRecursive()
            ->setPathmodeAbsolute()
            ->getAll();

        foreach($files as $file)
        {
            $this->indexFile(FileInfo::factory($file));
        }

        $subfolders = FileHelper::getSubfolders($path);

        foreach($subfolders as $subfolder)
        {
            $this->indexSubfolder(FolderInfo::factory($path.'/'.$subfolder));
        }
    }

    /**
     * @var LibraryFile[]|NULL
     */
    private ?array $files = null;

    /**
     * @var LibrarySubfolder[]
     */
    private array $folders = array();

    /**
     * @var array<int,array<int,array<int,LibraryFile>>>
     */
    private array $seasonIndex = array();

    private function indexFile(FileInfo $file) : void
    {
        $normalized = $this->parseName($file->getBaseName());

        if($normalized === null) {
            return;
        }

        $this->registerFile(new LibraryFile(
            $file,
            $normalized['name'],
            $normalized['season'],
            $normalized['episode']
        ));
    }

    private array $excludeFolders = array(
        '.sync',
        '.svn'
    );

    private function indexSubfolder(FolderInfo $subfolder) : void
    {
        if(in_array($subfolder->getName(), $this->excludeFolders, true)) {
            return;
        }

        $this->registerFolder(new LibrarySubfolder(
            $subfolder,
            $subfolder->getName(),
            $subfolder->getModifiedDate()
        ));
    }

    private function registerFolder(LibrarySubfolder $folder) : void
    {
        $this->folders[$folder->getName()] = $folder;
    }

    private function registerFile(LibraryFile $info) : void
    {
        $this->files[] = $info;

        $seasonNr = $info->getSeason();
        $episodeNr = $info->getEpisode();

        if(!isset($this->seasonIndex[$seasonNr])) {
            $this->seasonIndex[$seasonNr] = array();
        }

        if(!isset($this->seasonIndex[$seasonNr][$episodeNr])) {
            $this->seasonIndex[$seasonNr][$episodeNr] = array();
        }

        $this->seasonIndex[$seasonNr][$episodeNr][] = $info;
    }

    public function findEpisode(string $seriesName, int $seasonNr, int $episodeNr) : ?LibraryFile
    {
        $this->load();

        $normalized = self::normalizeName($seriesName);

        if(!isset($this->seasonIndex[$seasonNr][$episodeNr])) {
            return null;
        }

        foreach($this->seasonIndex[$seasonNr][$episodeNr] as $info)
        {
            if($info->getName() === $normalized) {
                return $info;
            }
        }

        return null;
    }

    /**
     * @return LibraryFile[]
     */
    public function getFiles() : array
    {
        return $this->load();
    }

    public static function normalizeName(string $name) : string
    {
        $name = mb_strtolower($name);

        if(strpos($name, ' - ') !== false) {
            $parts = explode(' - ', $name);
            array_shift($parts);
            $name = implode(' - ', $parts);
        }

        $replaces = array(
            '.' => ' ',
            '-' => ' ',
            '(' => ' ',
            ')' => ' ',
            '[' => ' ',
            ']' => ' ',
            '_' => ' ',
            '~' => ' ',
            '{' => ' ',
            '}' => ' ',
            '!' => ' ',
            '$' => ' ',
            '?' => ' ',
            '\'' => ''
        );

        $name = str_replace(array_keys($replaces), array_values($replaces), $name);

        while(strpos($name, '  ') !== false) {
            $name = str_replace('  ', ' ', $name);
        }

        return trim($name);
    }

    /**
     * @param string $name
     * @return array{name:string,season:int,episode:int}|null
     */
    public function parseName(string $name) : ?array
    {
        $name = self::normalizeName($name).' END';

        preg_match('/ s[ ]*([0-9]{1,2})[ ]*e[ ]*([0-9]{1,2})[ ]/U', $name, $matches);

        if(empty($matches[0])) {
            return null;
        }

        $parts = explode($matches[0], $name);
        $name = trim((string)array_shift($parts));

        $parts = ConvertHelper::explodeTrim(' ', $name);

        $keep = array();
        foreach($parts as $part) {
            if(mb_strlen($part) < 2) {
                continue;
            }

            $keep[] = $part;
        }

        return array(
            'name' => $this->getNameAlias(implode(' ', $keep)),
            'season' => (int)$matches[1],
            'episode' => (int)$matches[2]
        );
    }

    public function getNameAlias(string $name) : string
    {
        $aliases = $this->getNameAliases();

        return $aliases[$name] ?? $name;
    }

    /**
     * @return array<string,string>
     */
    public function getNameAliases() : array
    {
        if($this->aliasesFile->exists()) {
            return $this->aliasesFile->parse();
        }

        return array();
    }

    public function setNameAlias(string $name, string $alias) : self
    {
        $aliases = $this->getNameAliases();
        $aliases[$name] = $alias;
        $this->aliasesFile->putData($aliases, true);
        return $this;
    }

    public function cacheExists() : bool
    {
        return $this->cacheFile->exists();
    }

    /**
     * @var string[]|null
     */
    private ?array $cachedSeriesNames = null;

    /**
     * @return string[]
     */
    public function getSeriesNames() : array
    {
        if(isset($this->cachedSeriesNames)) {
            return $this->cachedSeriesNames;
        }

        $files = $this->getFiles();
        $names = array();

        foreach($files as $file)
        {
            $names[$file->getName()] = null;
        }

        ksort($names);

        $this->cachedSeriesNames = array_keys($names);

        return $this->cachedSeriesNames;
    }

    public function deleteNameAlias(string $name) : self
    {
        $aliases = $this->getNameAliases();
        unset($aliases[$name]);
        $this->aliasesFile->putData($aliases, true);
        return $this;
    }

    /**
     * @return LibrarySubfolder[]
     */
    public function getAvailableFolders(string $sortBy='name', string $sortDir='asc') : array
    {
        $this->loadCache();

        $result = $this->folders;

        usort($result, static function(LibrarySubfolder $a, LibrarySubfolder $b) use($sortBy, $sortDir) : int
        {
            $dir = 1;
            if($sortDir === 'desc') {
                $dir = -1;
            }

            if($sortBy === 'date')
            {
                $dateA = $a->getDate();
                $dateB = $b->getDate();

                if($dateA > $dateB) {
                    return -1 * $dir;
                }

                if($dateA < $dateB) {
                    return $dir;
                }

                return 0;
            }

            return strnatcasecmp($a->getName(), $b->getName()) * $dir;
        });

        return $result;
    }
}
