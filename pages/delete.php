<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

$manager = Manager::getInstance();
$series = $manager->getSeries();
$selected = $manager->getSelected();

$series->delete($selected);
$series->save();

header('Location:./');
exit;
