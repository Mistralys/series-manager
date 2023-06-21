<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use AppUtils\FileHelper\JSONFile;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Series;

class SeriesCollection
{
    protected JSONFile $dataFile;

    /**
     * @var Series[]
     */
    protected array $series = array();

    public function __construct()
    {
        $this->dataFile = JSONFile::factory(APP_ROOT . '/data/series.json');

        if ($this->dataFile->exists())
        {
            $data = $this->dataFile->parse();
            foreach ($data as $item)
            {
                $this->add($item);
            }
        }
    }

    public function add(array $data) : Series
    {
        $series = new Series($data);
        $this->series[] = $series;
        return $series;
    }

    /**
     * @return Series[]
     */
    public function getAll() : array
    {
        usort($this->series, array($this, 'handle_sortByName'));

        return $this->series;
    }

    public function handle_sortByName(Series $a, Series $b) : int
    {
        return strnatcasecmp($a->getName(), $b->getName());
    }

    public function getByIMDBID(string $id) : ?Series
    {
        foreach ($this->series as $item)
        {
            if ($item->getIMDBID() === $id)
            {
                return $item;
            }
        }

        return null;
    }

    public function IMDBIDExists(string $id) : bool
    {
        foreach ($this->series as $item)
        {
            if ($item->getIMDBID() === $id)
            {
                return true;
            }
        }

        return false;
    }

    public function delete(Series $targetSeries) : void
    {
        $keep = array();
        foreach ($this->series as $item)
        {
            if ($item->getIMDBID() !== $targetSeries->getIMDBID())
            {
                $keep[] = $item;
            }
        }

        $this->series = $keep;
    }

    public function save() : void
    {
        $data = array();
        foreach ($this->series as $item)
        {
            $data[] = $item->toArray();
        }

        $this->dataFile->putData($data, true);
    }

    public function fetchData(bool $clearCache = false) : array
    {
        $messages = array();

        $client = Manager::getInstance()->createClient();

        foreach ($this->series as $item)
        {
            $item->fetchData($client, $clearCache);
            $messages = array_merge($messages, $item->getMessages());
        }

        return $messages;
    }
}