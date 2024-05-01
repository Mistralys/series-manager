<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use Adrenth\Thetvdb\Client;
use Adrenth\Thetvdb\Model\BasicEpisode;
use AppUtils\FileHelper\JSONFile;
use AppUtils\OutputBuffering;
use Mistralys\SeriesManager\Manager;use function AppLocalize\pts;

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

    protected array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    protected function getKey(string $name, $default=null)
    {
        return $this->data[$name] ?? $default;
    }
    
    public function getName(bool $appendThe=true) : string
    {
        $name = (string)$this->getKey(self::KEY_NAME);

        if($appendThe && strpos($name, 'The') === 0)
        {
            $name = substr($name, 4) . ', The';
        }
        
        return $name;
    }

    public function isArchived() : bool
    {
        return $this->getKey(self::KEY_ARCHIVED) === true;
    }
    
    public function getStatus() : string
    {
        return (string)$this->getInfo(self::INFO_STATUS);
    }
    
    public function getTVDBAlias() : string
    {
        return (string)$this->getKey(self::KEY_TVDB_ALIAS);
    }
    
    public function getTVDBID()
    {
        return $this->getKey(self::KEY_TVDB_ID);
    }
    
    public function getTVDBLink() : ?string
    {
        $id = $this->getTVDBAlias();
        if($id) {
            return 'https://thetvdb.com/series/'.$id.'#seasons';
        }
        
        return null;
    }
    
    public function getIMDBID() : string
    {
        return (string)$this->getKey(self::KEY_IMDB_ID);
    }
    
    public function getRarbgLink() : ?string
    {
        $name = $this->getName();
        if($name) {
            return 'https://rargb.to/search/?'.http_build_query(array('search' => $name));
        }
        
        return null;
    }
    
    public function getIMDBLink() : ?string
    {
        $id = $this->getIMDBID();
        if($id) {
            return 'https://imdb.com/title/'.$id;
        }
        
        return null;
    }
    
    public function getLastDLSeason() : int
    {
        return (int)$this->getKey('lastDLSeason');
    }
    
    public function getLastDLEpisode() : int
    {
        return (int)$this->getKey('lastDLEpisode');
    }
    
    public function setLastDLSeason($season)
    {
        if(!preg_match('/\A[0-9]+\z/m', $season)) {
            return false;
        }
        
        return $this->setKey('lastDLSeason', $season);
    }

    protected function setKey(string $name, $value) : bool
    {
        $new = $value;
        $old = $this->getKey($name);
        
        if($new===null) {$new='';}
        if($old===null) {$old='';}
        
        if($new===$old) {
            return false;
        }
        
        $this->data[$name] = $new;
        return true;
    }
    
    public function setLastDLEpisode($episode)
    {
        if(!preg_match('/\A[0-9]+\z/m', $episode)) {
            return false;
        }
        
        return $this->setKey('lastDLEpisode', $episode);
    }
    
    public function toArray() : array
    {
        return $this->data;
    }
    
    public function fetchData(Client $client, bool $clearCache=false) : void
    {
        $id = $this->getTVDBID();
        if(empty($id)) {
            $this->addMessage('Cannot fetch data, no TVDB ID set.');
            return;
        }

        $this->resetInternalCache();

        $cacheFile = JSONFile::factory(APP_ROOT.'/cache/'.$this->getIMDBID().'-info.json');
        if($clearCache) {
            $cacheFile->delete();
        }

        if($cacheFile->exists()) {
            $this->addMessage('Fetched info from local cache.');
            $this->setKey(self::KEY_INFO, $cacheFile->parse());
            return;
        }

        $fetched = $client->series()->get((int)$id);

        $this->addMessage('Fetched info from online API.');

        $info = array(
            self::INFO_STATUS => $fetched->getStatus(),
            self::INFO_GENRE => $fetched->getGenre(),
            self::INFO_NETWORK => $fetched->getNetwork(),
            self::INFO_OVERVIEW => $fetched->getOverview(),
            self::INFO_SEASON => $fetched->getSeason(),
            self::INFO_FIRST_AIRED => $fetched->getFirstAired(),
            self::INFO_SITE_RATING => $fetched->getSiteRating(),
            self::INFO_SITE_RATING_COUNT => $fetched->getSiteRatingCount(),
            self::INFO_SEASONS => array()
        );

        $episodes = $client->series()->getEpisodes((int)$id)->getData();

        foreach($episodes as $episode)
        {
            /* @var BasicEpisode $episode */
            $season = $episode->getAiredSeason();

            if(!isset($info[self::INFO_SEASONS][$season]))
            {
                $info[self::INFO_SEASONS][$season] = array(
                    self::INFO_SEASON_ID => $episode->getAiredSeasonID(),
                    self::INFO_SEASON_EPISODES => array()
                );
            }

            $info[self::INFO_SEASONS][$season][self::INFO_SEASON_EPISODES][$episode->getAiredEpisodeNumber()] = array(
                self::INFO_EPISODE_ID => $episode->getId(),
                self::INFO_EPISODE_NAME => $episode->getEpisodeName(),
                self::INFO_EPISODE_OVERVIEW => $episode->getOverview()
            );
        }

        $cacheFile->putData($info, true);

        $this->setKey(self::KEY_INFO, $info);
    }

    private function resetInternalCache() : void
    {
        $this->seasons = null;
    }

    /**
     * @var Season[]|null
     */
    private ?array $seasons = null;

    /**
     * @return Season[]
     */
    public function getSeasons() : array
    {
        if(isset($this->seasons)) {
            return $this->seasons;
        }

        $this->seasons = array();

        $data = $this->getInfo(self::INFO_SEASONS);
        if(!is_array($data)) {
            return array();
        }

        foreach($data as $seasonNumber => $seasonData)
        {
            // Skip 0 seasons
            if($seasonNumber <= 0) {
                continue;
            }

            $this->seasons[] = new Season($this, $seasonNumber, $seasonData);
        }

        return $this->seasons;
     }

    /**
     * @return array<int,array{url:string,label:string}>
     */
    public function getLinks() : array
    {
        $links = array();

        $link = $this->getTVDBLink();
        if($link) {
            $links[] = array(
                'url' => $link,
                'label' => 'TVDB'
            );
        }
        
        $link = $this->getIMDBLink();
        if($link) {
            $links[] = array(
                'url' => $link,
                'label' => 'IMDB'
            );
        }

        return array_merge($links, Manager::getInstance()->prepareCustomLinks($this->getName(false)));
    }
    
    public function setName(string $name) : bool
    {
        return $this->setKey(self::KEY_NAME, $name);
    }

    public function setArchived(bool $archived) : self
    {
        $this->setKey(self::KEY_ARCHIVED, $archived);
        return $this;
    }

    public function save() : self
    {
        Manager::getInstance()->getSeries()->save();
        return $this;
    }

    public function setTVDBAlias(string $id) : bool
    {
        return $this->setKey(self::KEY_TVDB_ALIAS, $id);
    }

    public function setIMDBID(string $id) : bool
    {
        return $this->setKey(self::KEY_IMDB_ID, $id);
    }
    
    public function setTVDBID(string $id) : bool
    {
        return $this->setKey(self::KEY_TVDB_ID, $id);
    }

    /**
     * @var string[]
     */
    protected array $messages = array();
    
    protected function addMessage(string $message) : void
    {
        $this->messages[] = 'Series ['.$this->getName().'] | '.$message;
    }

    /**
     * @return string[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }
    
    public function countSeasons() : int
    {
        return count($this->getSeasons());
    }
    
    public function countEpisodes() : int
    {
        $seasons = $this->getSeasons();
        $total = 0;

        foreach($seasons as $season)
        {
            $total += $season->countEpisodes();
        }

        return $total;
    }

    public function getSynopsis() : string
    {
        return (string)$this->getInfo(self::INFO_OVERVIEW);
    }

    public function hasInfo() : bool
    {
        return !empty($this->getKey(self::KEY_INFO));
    }

    /**
     * @param string $key
     * @return string|number|bool|array|NULL
     */
    public function getInfo(string $key)
    {
        $info = $this->getKey(self::KEY_INFO);
        if(is_array($info) && isset($info[$key])) {
            return $info[$key];
        }

        return null;
    }

    public function getURLEdit(array $params=array()) : string
    {
        $params[Manager::REQUEST_PARAM_PAGE] = 'edit';

        return $this->getURL($params);
    }

    public function getURLEditTab(string $tabID, array $params=array()) : string
    {
        $params['tab'] = $tabID;

        return $this->getURLEdit($params);
    }

    public function getURLDelete(array $params=array()) : string
    {
        $params[Manager::REQUEST_PARAM_PAGE] = 'delete';

        return $this->getURL($params);
    }

    public function getURLArchive(?array $params=null, ?string $returnPage=null) : string
    {
        if($params === null) {
            $params = array();
        }

        $params[Manager::REQUEST_PARAM_PAGE] = 'archive';
        $params[Manager::REQUEST_VAR_RETURN_PAGE] = $returnPage;

        return $this->getURL($params);
    }

    public function getURLUnarchive(?array $params=null, ?string $returnPage=null) : string
    {
        if($params === null) {
            $params = array();
        }

        $params[Manager::REQUEST_PARAM_PAGE] = 'unarchive';
        $params[Manager::REQUEST_VAR_RETURN_PAGE] = $returnPage;

        return $this->getURL($params);
    }

    public function getURLFavorite(?array $params=null, ?string $returnPage=null) : string
    {
        if($params === null) {
            $params = array();
        }

        $params[Manager::REQUEST_PARAM_PAGE] = 'favorite';
        $params[Manager::REQUEST_VAR_RETURN_PAGE] = $returnPage;

        return $this->getURL($params);
    }

    public function getURLUnfavorite(?array $params=null, ?string $returnPage=null) : string
    {
        if($params === null) {
            $params = array();
        }

        $params[Manager::REQUEST_PARAM_PAGE] = 'unfavorite';
        $params[Manager::REQUEST_VAR_RETURN_PAGE] = $returnPage;

        return $this->getURL($params);
    }

    public function getURLFetch(array $params=array()) : string
    {
        $params['fetch'] = 'yes';

        return $this->getURLEditTab(self::EDIT_TAB_SEASONS, $params);
    }

    public function getURLClearAndFetch(array $params=array()) : string
    {
        $params['clear'] = 'yes';

        return $this->getURLFetch($params);
    }

    protected function getURL(array $params=array()) : string
    {
        $params['id'] = $this->getIMDBID();

        return '?'.http_build_query($params);
    }

    public function getCurrentSeason() : int
    {
        return (int)$this->getInfo(self::INFO_SEASON);
    }

    /**
     * @return string[]
     */
    public function getGenres() : array
    {
        $genres = $this->getInfo(self::INFO_GENRE);
        if(is_array($genres)) {
            return $genres;
        }

        return array();
    }

    public function isComplete() : bool
    {
        $seasons = $this->getSeasons();

        foreach($seasons as $season)
        {
            if(!$season->isComplete()) {
                return false;
            }
        }

        return true;
    }

    public function isFavorite() : bool
    {
        return $this->getKey(self::KEY_FAVORITE) === true;
    }

    public function setFavorite(bool $favorite) : self
    {
        $this->setKey(self::KEY_FAVORITE, $favorite);
        return $this;
    }

    public function renderFavoriteIcon() : string
    {
        OutputBuffering::start();

        if($this->isFavorite())
        {
            ?>
            <i class="glyphicon glyphicon-star" style="color:#ffdc00;font-size:80%"></i>
            <?php
        }
        else
        {
            ?>
            <i class="glyphicon glyphicon-star-empty text-muted" style="font-size:80%"></i>
            <?php
        }

        return OutputBuffering::get();
    }

    /**
     * @return never
     */
    public function redirectToReturnPage() : void
    {
        Manager::getInstance()->redirectToReturnPage(array(
            'id' => $this->getIMDBID()
        ));
    }

    public function getSearchText() : string
    {
        return implode(
            ' ',
            array(
                $this->getName(false),
                $this->getTVDBID(),
                $this->getIMDBID()
            )
        );
    }
}
