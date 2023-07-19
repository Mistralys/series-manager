<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection;
use AppUtils\OutputBuffering;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Manager\Library;
use Mistralys\SeriesManager\Manager\LibraryFile;use function AppLocalize\pt;

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

    public function getDownloadStatusIcon() : string
    {
        OutputBuffering::start();

        if($this->isFoundOnDisk())
        {
            ?>
            <i title="<?php pt('Found in the local library') ?>" class="glyphicon glyphicon-star" style="color: #ffda00;border: solid 1px #ffda00;border-radius: 3px;"></i>
            <?php
        }
        else if($this->isDownloaded())
        {
            ?>
            <i title="<?php pt('Downloaded according to the saved season and episode numbers') ?>" class="glyphicon glyphicon-ok-sign text-success"></i>
            <?php
        }
        else
        {
            ?>
            <i title="<?php pt('Never downloaded.') ?>" class="glyphicon glyphicon-remove-sign text-danger"></i>
            <?php
        }

        return OutputBuffering::get();
    }

    public function isFoundOnDisk() : bool
    {
        return $this->findInLibrary() !== null;
    }

    public function getSeries() : Series
    {
        return $this->getSeason()->getSeries();
    }

    public function getSeasonNumber() : int
    {
        return $this->getSeason()->getNumber();
    }

    public function findInLibrary() : ?LibraryFile
    {
        return Library::createFromConfig()->findEpisode(
            $this->getSeries()->getName(false),
            $this->getSeasonNumber(),
            $this->getNumber()
        );
    }

    public function getSearchString() : string
    {
        return sprintf(
            '%s s%02de%02d',
            $this->getSeason()->getSeries()->getName(false),
            $this->getSeason()->getNumber(),
            $this->getNumber()
        );
    }

    public function isComplete() : bool
    {
        return $this->findInLibrary() !== null;
    }
}
