<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use Adrenth\Thetvdb\Client;
use AppLocalize\Localization;
use AppUtils\FileHelper\JSONFile;
use AppUtils\Request;
use Mistralys\ChangelogParser\ChangelogParser;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\SeriesCollection;
use function AppLocalize\t;

class Manager
{
    public const REQUEST_PARAM_PAGE = 'page';
    const SESSION_DARK_MODE = 'darkmode';

    protected SeriesCollection $series;
    protected string $page = 'list';
    protected static ?Manager $instance = null;
    private bool $loggedIn = false;
    protected string $version = '0.0';

    /**
     * @var array<string,string>|NULL
     */
    protected ?array $pages = null;
    private Request $request;

    public static function getInstance() : Manager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new Manager();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->series = new SeriesCollection();
        $this->request = Request::getInstance();

        $changelogFile = __DIR__.'/../../changelog.md';
        $versionFile = __DIR__.'/../../version.txt';

        if(filemtime($changelogFile) > filemtime($versionFile)) {
            $version = ChangelogParser::parseMarkdownFile($changelogFile)->getLatestVersion();
            if($version !== null) {
                file_put_contents($versionFile, $version->getNumber());
            }
        }

        $this->version = file_get_contents($versionFile);

        session_start();
    }

    public static function isDarkMode() : bool
    {
        return !empty($_SESSION[self::SESSION_DARK_MODE]) && $_SESSION[self::SESSION_DARK_MODE] === true;
    }

    public static function getName() : string
    {
        return t('Series Manager');
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getPages() : array
    {
        if(!isset($this->pages)) {
            $this->pages = array(
                'list' => t('Overview'),
                'archived' => t('Archive'),
                'add' => t('Add new'),
                'fetch' => t('Fetch data'),
                'library' => t('Library'),
                'login' => t('Login')
            );
        }
        return $this->pages;
    }

    public function getPageID() : string
    {
        return $this->page;
    }

    public function start() : void
    {
        self::initLocalization();

        $this->selectLocale();
        $this->checkDarkModeToggle();

        $page = $_REQUEST[self::REQUEST_PARAM_PAGE] ?? $this->page;

        if (!$this->checkLogin())
        {
            $page = 'login';
        }

        $pages = $this->getPages();
        if (isset($pages[$page]))
        {
            $this->page = $page;
        }

        $editPages = array(
            'delete',
            'edit',
            'archive',
            'unarchive',
            'favorite',
            'unfavorite'
        );

        if (in_array($page, $editPages, true) && $this->getSelectedID())
        {
            $this->page = $page;
        }

        ob_start();
        require APP_ROOT . '/pages/' . $this->page . '.php';
        $this->content = ob_get_clean();

        require APP_ROOT . '/pages/_frame.php';
    }

    /**
     * @var string
     */
    protected $content = '';

    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @return SeriesCollection
     */
    public function getSeries() : SeriesCollection
    {
        return $this->series;
    }

    public function getSelectedID() : string
    {
        if (isset($_REQUEST['id']) && $this->series->IMDBIDExists($_REQUEST['id']))
        {
            return $_REQUEST['id'];
        }

        return '';
    }

    public function getSelected() : ?Series
    {
        $id = $this->getSelectedID();
        if ($id)
        {
            return $this->series->getByIMDBID($id);
        }

        return null;
    }

    private function checkLogin() : bool
    {
        if (!isset($_SESSION['auth']))
        {
            return false;
        }

        $authKey = $this->encodePassword(APP_PASSWORD);

        if ($_SESSION['auth'] === $authKey)
        {
            $this->loggedIn = true;
            return true;
        }

        return false;
    }

    public function isLoggedIn() : bool
    {
        return $this->loggedIn;
    }

    public function isPasswordValid(string $password) : bool
    {
        return $password === APP_PASSWORD;
    }

    public function encodePassword(string $password) : string
    {
        return sha1($password . '-' . APP_SALT);
    }

    private ?Client $client = null;

    public function createClient() : Client
    {
        if (isset($this->client))
        {
            return $this->client;
        }

        $client = new Client();
        $client->setLanguage('en');

        $token = $client->authentication()->login(
            APP_API_KEY,
            null,
            APP_SUBSCRIBER_PIN
        );

        $client->setToken($token);

        $this->client = $client;

        return $client;
    }

    /**
     * @var array<int,array{label:string,template:string}>|null
     */
    private ?array $linkDefs = null;

    public function getCustomLinkDefs() : array
    {
        if(isset($this->linkDefs)) {
            return $this->linkDefs;
        }

        $file = JSONFile::factory(__DIR__.'/../../search-links.json');

        if(!$file->exists()) {
            return array();
        }

        $linkDefs = $file->parse();
        $this->linkDefs = array();

        foreach($linkDefs as $def)
        {
            if(!isset($def['template'], $def['label'])) {
                continue;
            }

            $this->linkDefs[] = array(
                'template' => (string)$def['template'],
                'label' => (string)$def['label']
            );
        }

        return $this->linkDefs;
    }

    /**
     * @param string $searchTerm
     * @return array<int,array{label:string,url:string}>
     */
    public function prepareCustomLinks(string $searchTerm) : array
    {
        return $this->prepareLinks($this->getCustomLinkDefs(), $searchTerm);
    }

    /**
     * @param array<int,array{label:string,template:string}> $linkDefs
     * @return array<int,array{label:string,url:string}>
     */
    public function prepareLinks(array $linkDefs, string $searchTerm) : array
    {
        $variables = array(
            '{SEARCH}' => rawurlencode($searchTerm)
        );

        $keys = array_keys($variables);
        $values = array_values($variables);
        $result = array();

        foreach($linkDefs as $linkDef)
        {
            $url = str_replace(
                $keys,
                $values,
                $linkDef['template']
            );

            $result[] = array(
                'label' => $linkDef['label'],
                'url' => $url
            );
        }

        return $result;
    }

    public function getURLAdd(array $params=array()) : string
    {
        $params[self::REQUEST_PARAM_PAGE] = 'add';
        return '?'.http_build_query($params);
    }

    public function getURL(array $params=array()) : string
    {
        return '?'.http_build_query($params);
    }

    public static function initLocalization() : void
    {
        // add the locales we wish to manage (en_UK is always present)
        Localization::addAppLocale('de_DE');
        Localization::addAppLocale('fr_FR');

        Localization::addSourceFolder(
            'series-manager-classes',
            'Series Manager',
            'Series Manager',
            __DIR__.'/../../localization',
            __DIR__.'/../../'
        )
            ->excludeFolder('cache')
            ->excludeFolder('css')
            ->excludeFolder('js')
            ->excludeFolder('fonts')
            ->excludeFolder('data')
            ->excludeFolder('tests')
            ->excludeFolder('vendor');

        // has to be called last after all sources and locales have been configured
        Localization::configure(__DIR__.'/../../localization/storage.json', '');
    }

    private function selectLocale() : void
    {
        $locale = $_REQUEST['selectLocale'] ?? $_SESSION['locale'] ?? null;

        if($locale && Localization::appLocaleExists($locale)) {
            Localization::selectAppLocale($locale);
            $_SESSION['locale'] = $locale;
        }
    }

    private function checkDarkModeToggle() : void
    {
        if(!$this->request->getBool('toggleDarkMode'))
        {
            return;
        }

        Manager::getInstance()->setDarkMode(!self::isDarkMode());

        header('Location:./');
        exit;
    }

    public function setDarkMode(bool $enabled) : self
    {
        $_SESSION[self::SESSION_DARK_MODE] = $enabled;
        return $this;
    }
}
