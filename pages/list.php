<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use AppUtils\JSHelper;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\ptex;

$manager = Manager::getInstance();
$series = $manager->getSeries();

if(isset($_REQUEST['update'], $_REQUEST['series']) && $_REQUEST['update'] === 'yes') {
    foreach($_REQUEST['series'] as $imdbID => $data) {
        $item = $series->getByIMDBID($imdbID);
        $item->setLastDLSeason($data['lastDLSeason']);
        $item->setLastDLEpisode($data['lastDLEpisode']);
    }

    $series->save();
    header('Location:./');
    exit;
}

$items = $series->getAll();

if(empty($items)) {
    ?>
    <div class="alert alert-info">
        <?php pt('No series found.') ?>
    </div>
    <?php
    return;
} 

?>
<h3><?php pt('Available series') ?></h3>
<form method="post" class="form-inline">
    <table class="table table-hover">
        <thead>
            <tr>
                <td><?php pt('Name') ?></td>
                <td><?php pt('Status') ?></td>
                <td style="text-align: right"><?php pt('Seasons') ?></td>
                <td style="text-align: right"><?php pt('Episodes') ?></td>
                <td style="text-align: center"><?php pt('Complete?') ?></td>
                <td><?php pt('Links') ?></td>
                <td><?php pt('Last Downloaded') ?></td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($items as $item)
            {
                $rowID = JSHelper::nextElementID();

                ?>
                <tr>
                    <td><a href="<?php echo $item->getURLEdit() ?>"><?php echo $item->getName() ?></a></td>
                    <td><?php echo $item->getStatus() ?></td>
                    <td style="text-align: right"><?php echo $item->countSeasons() ?></td>
                    <td style="text-align: right"><?php echo $item->countEpisodes() ?></td>
                    <td style="text-align: center"><?php echo UI::prettyBool($item->isComplete()) ?></td>
                    <td>
                        <?php
                        $links = $item->getLinks();
                        $tokens = array();
                        foreach($links as $link) {
                            $tokens[] = 
                            '<a href="'.$link['url'].'" target="_blank">'.
                                $link['label'].
                            '</a>';
                        }
                        
                        echo implode(' | ', $tokens);
                        ?>
                    </td>
                    <td>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="<?php echo $rowID ?>-lastDLSeason">
                                        <?php ptex('S', 'Single letter used to represent a `Season`.') ?>
                                    </label>
                                </div>
                                <input  name="series[<?php echo $item->getIMDBID() ?>][lastDLSeason]"
                                        id="<?php echo $rowID ?>-lastDLSeason"
                                        type="number"
                                        class="form-control"
                                        value="<?php echo $item->getLastDLSeason() ?>"
                                        style="width:60px"/>
                            </div>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="<?php echo $rowID ?>-lastDLEpisode">
                                        <?php ptex('E', 'Single letter used to represent an `Episode`.') ?>
                                    </label>
                                </div>
                                <input  name="series[<?php echo $item->getIMDBID() ?>][lastDLEpisode]"
                                        id="<?php echo $rowID ?>-lastDLEpisode"
                                        type="number"
                                        class="form-control"
                                        value="<?php echo $item->getLastDLEpisode() ?>"
                                        style="width:60px"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="<?php echo $item->getURLArchive() ?>" class="btn btn-default">
                            <i class="glyphicon glyphicon-bookmark"></i>
                        </a>
                        <a href="<?php echo $item->getURLDelete() ?>" class="btn btn-danger">
                            <i class="glyphicon glyphicon-remove-sign"></i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <p>
        <button name="update" type="submit" class="btn btn-primary" value="yes">
            <i class="glyphicon glyphicon-edit"></i>
            <?php pt('Save progress') ?>
        </button>
    </p>
</form>
