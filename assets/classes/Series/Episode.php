<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\JSONFile;
use Mistralys\SeriesManager\Manager;

class Episode
{
    private int $number;
    private ArrayDataCollection $data;
    private Season $season;

    public function __construct(Season $season, int $number, array $data)
    {
        $this->number = $number;
        $this->season = $season;
        $this->data = ArrayDataCollection::create($data);
    }

    public function getSeason() : Season
    {
        return $this->season;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    public function getID() : int
    {
        return $this->data->getInt(Series::INFO_EPISODE_ID);
    }

    public function getName() : string
    {
        return $this->data->getString(Series::INFO_EPISODE_NAME);
    }

    public function getSynopsis() : string
    {
        return $this->data->getString(Series::INFO_EPISODE_OVERVIEW);
    }

    /**
     * @return array<int,array{label:string,url:string}>
     */
    public function getSearchLinks() : array
    {
        return Manager::getInstance()->prepareCustomLinks($this->getSearchString());
    }

    public function isDownloaded() : bool
    {
        $series = $this->getSeason()->getSeries();

        $mySeason = $this->getSeason()->getNumber();
        $latestSeason = $series->getLastDLSeason();
        $lastestEpisode = $series->getLastDLEpisode();

        if($mySeason > $latestSeason) {
            return false;
        }

        if($mySeason < $latestSeason) {
            return true;
        }

        return $this->getNumber() <= $lastestEpisode;
    }

    public function getSearchString() : string
    {
        return sprintf(
            '%s s%02de%02d',
            $this->getSeason()->getSeries()->getName(),
            $this->getSeason()->getNumber(),
            $this->getNumber()
        );
    }
}
