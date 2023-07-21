<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

$selected = Manager::getInstance()->getSelected();

$selected->setArchived(true)->save();

header('Location:./');
exit;
