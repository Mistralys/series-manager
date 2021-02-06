<?php

$manager = Manager::getInstance();
$series = $manager->getSeries();
$selected = $manager->getSelected();

$series->delete($selected);
$series->save();

header('Location:./');
exit;
