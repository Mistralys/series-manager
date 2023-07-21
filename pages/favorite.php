<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

Manager::getInstance()
    ->getSelected()
    ->setFavorite(true)
    ->save();

header('Location:./');
exit;
