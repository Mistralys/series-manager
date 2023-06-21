<?php 

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\SeriesForm;

$manager = Manager::getInstance();
$selected = $manager->getSelected();

if($selected === null) {
    die('No series selected.');
}

if(isset($_REQUEST['fetch']) && $_REQUEST['fetch'] === 'yes')
{
    $client = $manager->createClient();
    $selected->fetchData($client);
    $manager->getSeries()->save();

    header('Location:'.$selected->getURLEdit());
}

?>
<h3><?php echo $selected->getName() ?></h3>
<?php

if($selected->hasInfo())
{
    ?>
    <table class="table">
        <tbody>
            <tr>
                <th>Synopsis</th>
                <td><?php echo $selected->getSynopsis() ?></td>
            </tr>
            <tr>
                <th>Current season</th>
                <td><?php echo $selected->getCurrentSeason() ?></td>
            </tr>
            <tr>
                <th>Genres</th>
                <td><?php echo implode(', ', $selected->getGenres()) ?></td>
            </tr>
        </tbody>
    </table>
    <?php
}
?>
<a href="<?php echo $selected->getURLFetch() ?>" class="btn btn-primary">
    <i class="glyphicon glyphicon-download"></i>
    Fetch data
</a>
<hr>
<?php

(new SeriesForm($selected))->display();
