<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\ArrayDataCollection;

class Episode
{
    private int $number;
    private ArrayDataCollection $data;

    public function __construct(int $number, array $data)
    {
        $this->number = $number;
        $this->data = ArrayDataCollection::create($data);
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
}
