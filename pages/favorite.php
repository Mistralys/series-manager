<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

$manager = Manager::getInstance();

$manager
    ->getSelected()
    ->setFavorite(true)
    ->save()
    ->redirectToReturnPage();
