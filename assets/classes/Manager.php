<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use Adrenth\Thetvdb\Client;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\SeriesCollection;

class Manager
{
    /**
     * @var SeriesCollection
     */
    protected $series;

    /**
     * @var string
     */
    protected $page = 'list';

    /**
     * @var Manager
     */
    protected static $instance;

    /**
     * @var boolean
     */
    private $loggedIn = false;

    /**
     * @var array<string,string>
     */
    protected $pages = array(
        'list' => 'Home',
        'add' => 'Add new',
        'fetch' => 'Fetch data',
        'login' => 'Login'
    );

    /**
     * @var string
     */
    protected $version;

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
        $page = $_REQUEST['page'] ?? $this->page;

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
}
