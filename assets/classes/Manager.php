<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use Adrenth\Thetvdb\Client;
use AppUtils\FileHelper\JSONFile;
use AppUtils\Request;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\SeriesCollection;

class Manager
{
    public const REQUEST_PARAM_PAGE = 'page';

    protected SeriesCollection $series;
    protected string $page = 'list';
    protected static ?Manager $instance = null;
    private bool $loggedIn = false;
    protected string $version;

    /**
     * @var array<string,string>
     */
    protected array $pages = array(
        'list' => 'Home',
        'add' => 'Add new',
        'fetch' => 'Fetch data',
        'library' => 'Library',
        'login' => 'Login'
    );

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
        $this->version = file_get_contents(APP_ROOT . '/version.txt');

        session_start();
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function getPages() : array
    {
        return $this->pages;
    }

    public function getPageID() : string
    {
        return $this->page;
    }

    public function start() : void
    {
        $page = $_REQUEST[self::REQUEST_PARAM_PAGE] ?? $this->page;

        if (!$this->checkLogin())
        {
            $page = 'login';
        }

        if (isset($this->pages[$page]))
        {
            $this->page = $page;
        }

        if ($page === 'delete' && $this->getSelectedID())
        {
            $this->page = 'delete';
        }

        if ($page === 'edit' && $this->getSelectedID())
        {
            $this->page = 'edit';
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
}
