<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection;

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
}
