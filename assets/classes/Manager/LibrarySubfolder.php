<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Manager;

use AppUtils\FileHelper\FolderInfo;
use AppUtils\Microtime;
use DateTime;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\Series\SeriesForm;
use function AppUtils\sb;

class LibrarySubfolder
{
    public const KEY_PATH = 'path';
    public const KEY_NAME = 'name';
    public const KEY_DATE = 'date';

    private FolderInfo $folder;
    private string $name;
    private DateTime $date;
    private ?Series $series = null;
    private bool $seriesSearched = false;

    public function __construct(FolderInfo $folder, string $name, DateTime $date)
    {
        $this->folder = $folder;
        $this->name = $name;
        $this->date = $date;
    }

    public function getPath() : FolderInfo
    {
        return $this->folder;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getNameLinked() : string
    {
        $series = $this->detectSeries();

        if($series !== null) {
            return (string)sb()
                ->link($this->getName(), $series->getURLEdit());
        }

        return $this->getName();
    }

    public function getURLAdd(array $params=array()) : string
    {
        $params[SeriesForm::REQUEST_PARAM_NAME_SEARCH] = $this->getName();

        return Manager::getInstance()->getURLAdd($params);
    }

    public function getDate() : DateTime
    {
        return $this->date;
    }

    public function exists() : bool
    {
        return $this->detectSeries() !== null;
    }

    public function detectSeries() : ?Series
    {
        if($this->seriesSearched) {
            return $this->series;
        }

        $this->seriesSearched = true;

        $list = Manager::getInstance()->getSeries()->getAll();
        $compare = Library::normalizeName(mb_strtolower($this->getName()));

        foreach($list as $series)
        {
            if(Library::normalizeName(mb_strtolower($series->getName(false))) === $compare)
            {
                $this->series = $series;
            }
        }

        return $this->series;
    }

    public function toArray() : array
    {
        return array(
            self::KEY_PATH => $this->folder->getPath(),
            self::KEY_NAME => $this->name,
            self::KEY_DATE => Microtime::createFromDate($this->date)->getISODate()
        );
    }

    public static function createFromArray(array $def) : LibrarySubfolder
    {
        return new LibrarySubfolder(
            FolderInfo::factory($def[self::KEY_PATH]),
            $def[self::KEY_NAME],
            Microtime::createFromString($def[self::KEY_DATE])
        );
    }
}