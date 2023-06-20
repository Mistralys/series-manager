<?php

class Series_Series
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    protected function getKey($name, $default=null)
    {
        if(isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        return $default;
    }
    
    public function getName()
    {
        $name = $this->getKey('name');
        if(strpos($name, 'The') === 0) {
            $name = substr($name, 4).', The';
        }
        
        return $name;
    }
    
    public function getStatus()
    {
        $info = $this->getKey('info');
        return $info['status'] ?? null;
    }
    
    public function getTvcomID()
    {
        return $this->getKey('tvcom');
    }
    
    public function getTvcomLink()
    {
        $id = $this->getTvcomID();
        if($id) {
            return 'http://tv.com/shows/'.$id.'/episodes/';
        }
        
        return null;
    }
    
    public function getTvdbID()
    {
        return $this->getKey('tvdb');
    }
    
    public function getTvdbLink() : ?string
    {
        $id = $this->getTvdbID();
        if($id) {
            return 'https://thetvdb.com/index.php?id='.$id.'&lid=7#seasons';
        }
        
        return null;
    }
    
    public function getRarbgID() : string
    {
        return (string)$this->getKey('rarbg');
    }
    
    public function getRarbgLink() : ?string
    {
        $id = $this->getRarbgID();
        if($id) {
            return 'https://rarbg.to/tv/'.$id.'/';
        }
        
        return null;
    }
    
    public function getIMDBID()
    {
        return $this->getRarbgID();
    }
    
    public function getIMDBLink()
    {
        $id = $this->getRarbgID();
        if($id) {
            return 'https://imdb.com/title/'.$id;
        }
        
        return null;
    }
    
    public function getLastDLSeason()
    {
        return $this->getKey('lastDLSeason');
    }
    
    public function getLastDLEpisode()
    {
        return $this->getKey('lastDLEpisode');
    }
    
    public function setLastDLSeason($season)
    {
        if(!preg_match('/\A[0-9]+\z/m', $season)) {
            return false;
        }
        
        return $this->setKey('lastDLSeason', $season);
    }

    protected function setKey($name, $value)
    {
        $new = $value;
        $old = $this->getKey($name);
        
        if($new==null) {$new='';}
        if($old==null) {$old='';}
        
        if($new==$old) {
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
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function fetchData($clearCache=false)
    {
        $tvdbID = $this->getTvdbID();
        if(empty($tvdbID)) {
            $this->addMessage('Cannot fetch data, no TVDB ID set.');
            return;
        }
        
        $cacheFile = APP_ROOT.'/cache/'.$this->getRarbgID().'.info.xml';
        if($clearCache && file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        
        if(!file_exists($cacheFile)) {
            $url = 'http://thetvdb.com/api/'.APP_TVDB_API_KEY.'/series/'.$this->getTvdbID().'/all/en.xml';
            $content = @file_get_contents($url);
            if(!$content) {
                $this->addMessage('Failed to retrieve XML from API. Wrong ID?');
                return;
            }
            
            file_put_contents($cacheFile, $content);
            
            $this->addMessage('Fetched info from online API.');
        } else {
            $content = file_get_contents($cacheFile);
            $this->addMessage('Fetched info from local cache.');
        }

        try
        {
            $xml = new SimpleXMLElement($content);
        } 
        catch(Exception $e) 
        {
            $this->addMessage('Could not read the XML response.');
            return;
        }
        
        $info = array(
            'genres' => explode('|', trim((string)$xml->Series->Genre, '|')),
            'status' => (string)$xml->Series->Status,
            'seasons' => array()
        );
        
        foreach($xml->Episode as $episode) {
            $season = (string)$episode->SeasonNumber;
            if(!isset($info['seasons'][$season])) {
                $info['seasons'][$season] = array();
            }
            
            $episodeNr = (string)$episode->EpisodeNumber;
            $info['seasons'][$season][$episodeNr] = array(
                'name' => (string)$episode->EpisodeName,
                'synopsis' => (string)$episode->Overview
            );
        }
        
        $this->setKey('info', $info);
    }
    
    public function getLinks()
    {
        $links = array();
        $link = $this->getTvcomLink();
        if($link) {
            $links[] = array(
                'url' => $link,
                'label' => 'TV.com'
            );
        }
        
        $link = $this->getTvdbLink();
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
        
        $link = $this->getRarbgLink();
        if($link) {
            $links[] = array(
                'url' => $link,
                'label' => 'RARBG'
            );
        }
        return $links;
    }
    
    public function setName($name)
    {
        return $this->setKey('name', $name);
    }
    
    public function setTvcomID($id)
    {
        return $this->setKey('tvcom', $id);
    }

    public function setRarbgID($id)
    {
        return $this->setKey('rarbg', $id);
    }
    
    public function setTvdbID($id)
    {
        return $this->setKey('tvdb', $id);
    }
    
    protected $messages = array();
    
    protected function addMessage($message)
    {
        $this->messages[] = 'Series ['.$this->getName().'] | '.$message;  
    }
    
    public function getMessages()
    {
        return $this->messages;
    }
    
    public function countSeasons()
    {
        $info = $this->getKey('info');
        if($info) {
            return count($info['seasons']);
        }
        
        return 0;
    }
    
    public function countEpisodes()
    {
        $info = $this->getKey('info');
        if(!$info) {
            return 0;
        }
        
        $total = 0;
        foreach($info['seasons'] as $episodes) {
            $total += count($episodes);
        }
        
        return $total;
    }
}