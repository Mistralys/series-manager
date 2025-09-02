<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection;
use Mistralys\SeriesManager\Manager;

class Season
{
    private int $number;
    private ArrayDataCollection $data;

    /**
     * @var Episode[]|null
     */
    private ?array $episodes = null;
    private Series $series;

    public function __construct(Series $series, int $number, array $data)
    {
        $this->number = $number;
        $this->series = $series;
        $this->data = ArrayDataCollection::create($data);
    }

    public function getSeries() : Series
    {
        return $this->series;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    /**
     * @return array<int,array{label:string,url:string}>
     */
    public function getSearchLinks() : array
    {
        return Manager::getInstance()->prepareCustomLinks($this->getSearchString());
    }

    public function getSearchString() : string
    {
        return sprintf(
            '%s s%02d',
            $this->getSeries()->getName(),
            $this->getNumber()
        );
    }

    public function getID() : int
    {
        return $this->data->getInt(Series::INFO_SEASON_ID);
    }

    /**
     * @return Episode[]
     */
    public function getEpisodes() : array
    {
        if(isset($this->episodes)) {
            return $this->episodes;
        }

        $this->episodes = array();

        $data = $this->data->getArray(Series::INFO_SEASON_EPISODES);

        foreach($data as $episodeNumber => $episodeData)
        {
            $this->episodes[] = new Episode($this, $episodeNumber, $episodeData);
        }

        return $this->episodes;
    }

    public function countEpisodes() : int
    {
        return count($this->getEpisodes());
    }

    public function isComplete() : bool
    {
        $episodes = $this->getEpisodes();

        foreach($episodes as $episode)
        {
            if(!$episode->isComplete()) {
                return false;
            }
        }

        return true;
    }

    public function getURLDelete() : string
    {
        return $this->series->getURLSeasons(array(
            'season' => $this->getID(),
            'action' => 'delete-season'
        ));
    }

    public function delete() : void
    {
        $this->series->deleteSeason($this);
    }
}
