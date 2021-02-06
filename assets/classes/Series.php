<?php

class Series
{
    protected $dataFile;
    
    protected $series = array();
    
    public function __construct()
    {
        $this->dataFile = APP_ROOT.'/data/series.json';
        
        if(file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            foreach($data as $item) {
                $this->add($item);
            }
        }
    }   
    
    public function add($data)
    {
        $series = new Series_Series($data);
        $this->series[] = $series;
        return $series;
    }
    
    public function getAll()
    {
        usort($this->series, array($this, 'handle_sortByName'));
        
        return $this->series;
    }
    
    public function handle_sortByName(Series_Series $a, Series_Series $b)
    {
        return strnatcasecmp($a->getName(), $b->getName());
    }
    
    public function getByRarbgID($id)
    {
        foreach($this->series as $item) {
            if($item->getRarbgID() == $id) {
                return $item;
            }
        }
        
        return null;
    }

    public function rarbgIDExists($id)
    {
        foreach($this->series as $item) {
            if($item->getRarbgID() == $id) {
                return true;
            }
        }
    
        return false;
    }
    
    public function delete(Series_Series $targetSeries)
    {
        $keep = array();
        foreach($this->series as $item) {
            if($item->getRarbgID() != $targetSeries->getRarbgID()) {
                $keep[] = $item;
            }
        }
        
        $this->series = $keep;
    }
    
    public function save()
    {
        $data = array();
        foreach($this->series as $item) {
            $data[] = $item->toArray();
        }
        
        file_put_contents($this->dataFile, json_encode($data));
    }
    
    public function fetchData($clearCache=false)
    {
        $cacheFolder = APP_ROOT.'/cache';
        if(!is_dir($cacheFolder)) {
            mkdir($cacheFolder, 0777);
        }
        
        $messages = array();
        
        foreach($this->series as $item) {
            $item->fetchData($clearCache);
            $messages = array_merge($messages, $item->getMessages());
        }
        
        return $messages;
    }
}