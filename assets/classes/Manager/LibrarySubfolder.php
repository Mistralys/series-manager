<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Manager;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime;
use DateTime;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\ManagerException;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\Series\SeriesForm;
use Mistralys\SeriesManager\UI;
use function AppUtils\sb;

class LibrarySubfolder
{
    public const KEY_PATH = 'path';
    public const KEY_NAME = 'name';
    public const KEY_DATE = 'date';
    public const KEY_LIBRARY_FOLDER = 'libraryFolderName';

    private FolderInfo $folder;
    private string $name;
    private DateTime $date;
    private ?Series $series = null;
    private bool $seriesSearched = false;
    private string $libraryFolderName;

    public function __construct(string $libraryFolderName, FolderInfo $folder, string $name, DateTime $date)
    {
        $this->libraryFolderName = $libraryFolderName;
        $this->folder = $folder;
        $this->name = $name;
        $this->date = $date;
    }

    public function getPath() : FolderInfo
    {
        return $this->folder;
    }

    public function getLibraryFolderName() : string
    {
        return $this->libraryFolderName;
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
            if(Library::normalizeName(mb_strtolower($series->getName())) === $compare)
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
            self::KEY_DATE => Microtime::createFromDate($this->date)->getISODate(),
            self::KEY_LIBRARY_FOLDER => $this->getLibraryFolderName()
        );
    }

    public static function createFromArray(array $def) : ?LibrarySubfolder
    {
        if(!file_exists($def[self::KEY_PATH])) {
            return null;
        }

        try {
            return new LibrarySubfolder(
                $def[self::KEY_LIBRARY_FOLDER] ?? '',
                FolderInfo::factory($def[self::KEY_PATH]),
                $def[self::KEY_NAME],
                Microtime::createFromString($def[self::KEY_DATE])
            );
        }
        catch(FileHelper_Exception $e)
        {
            if($e->getCode() === FileHelper::ERROR_PATH_IS_NOT_A_FOLDER) {
                echo sprintf(
                    'Cannot open stored library folder, it is a file instead of a folder. '.PHP_EOL.
                    'Target folder: [%s] '.PHP_EOL.
                    'Library entry data: '.PHP_EOL.
                    '%s',
                    $def[self::KEY_PATH],
                    JSONConverter::var2json($def, JSON_PRETTY_PRINT)
                );

                return null;
            }

            throw $e;
        }
    }
}
