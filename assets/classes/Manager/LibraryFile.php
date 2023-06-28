<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Manager;

use AppUtils\FileHelper\FileInfo;
use Mistralys\SeriesManager\ManagerException;

class LibraryFile
{
    public const ERROR_INVALID_DATA_ARRAY = 1389011;

    public const KEY_FILE_PATH = 'p';
    public const KEY_NAME = 'n';
    public const KEY_SEASON = 's';
    public const KEY_EPISODE = 'e';

    private FileInfo $file;
    private string $name;
    private int $season;
    private int $episode;

    public function __construct(FileInfo $file, string $name, int $season, int $episode)
    {
        $this->file = $file;
        $this->name = $name;
        $this->season = $season;
        $this->episode = $episode;
    }

    public function getFile() : FileInfo
    {
        return $this->file;
    }

    public function getSeason() : int
    {
        return $this->season;
    }

    public function getEpisode() : int
    {
        return $this->episode;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getEpisodeName() : string
    {
        return sprintf(
            'S%02dE%02d',
            $this->getSeason(),
            $this->getEpisode()
        );
    }

    public function getNameWithEpisode() : string
    {
        return sprintf(
            '%s %s',
            $this->getName(),
            $this->getEpisodeName()
        );
    }

    /**
     * Serializes the file's data to an array, meant to
     * be compact (keys are very short).
     *
     * @return array<string,string|int>
     */
    public function toArray() : array
    {
        return array(
            self::KEY_FILE_PATH => $this->getFile()->getPath(),
            self::KEY_NAME => $this->getName(),
            self::KEY_SEASON => $this->getSeason(),
            self::KEY_EPISODE => $this->getEpisode()
        );
    }

    public static function createFromArray(array $data) : LibraryFile
    {
        if(
            isset(
                $data[self::KEY_FILE_PATH]
            )
            &&
            is_string($data[self::KEY_FILE_PATH])
            &&
            is_string($data[self::KEY_NAME])
            &&
            is_int($data[self::KEY_SEASON])
            &&
            is_int($data[self::KEY_EPISODE])
        ) {
            return new LibraryFile(
                FileInfo::factory($data[self::KEY_FILE_PATH]),
                $data[self::KEY_NAME],
                $data[self::KEY_SEASON],
                $data[self::KEY_EPISODE]
            );
        }

        throw new LibraryException(
            'Cannot create library file from an invalid array.',
            '',
            self::ERROR_INVALID_DATA_ARRAY
        );
    }
}
