<?php 

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use AppUtils\Request;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Episode;
use Mistralys\SeriesManager\Series\Season;
use Mistralys\SeriesManager\Series\SeriesForm;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\t;
use function AppUtils\sb;

$request = Request::getInstance();
$manager = Manager::getInstance();
$selected = $manager->getSelected();

if($selected === null) {
    die('No series selected.');
}

if($request->getBool('fetch'))
{
    $client = $manager->createClient();
    $selected->fetchData($client, $request->getBool('clear'));
    $manager->getSeries()->save();

    header('Location:'.$selected->getURLEdit());
}

?>
<h3><?php echo $selected->getName() ?></h3>
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab"><?php pt('Summary'); ?></a>
    </li>
    <li role="presentation">
        <a href="#seasons" aria-controls="seasons" role="tab" data-toggle="tab"><?php pt('Seasons') ?></a>
    </li>
    <li role="presentation">
        <a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><?php pt('Settings') ?></a>
    </li>
</ul>
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="summary">
        <?php

        if($selected->hasInfo())
        {
            ?>
            <table class="table table-properties">
                <tbody>
                    <tr>
                        <td><?php echo $selected->getStatus() ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $selected->getSynopsis() ?></td>
                    </tr>
                    <tr>
                        <th><?php pt('Genres') ?></th>
                        <td><?php echo implode(', ', $selected->getGenres()) ?></td>
                    </tr>
                    <tr>
                        <th><?php pt('Complete?') ?></th>
                        <td><?php echo UI::prettyBool($selected->isComplete()) ?></td>
                    </tr>
                    <tr>
                        <th><?php pt('Current season') ?></th>
                        <td><?php echo $selected->getCurrentSeason() ?></td>
                    </tr>
                    <tr>
                        <th><?php pt('Links') ?></th>
                        <td>
                            <a href="<?php echo $selected->getIMDBLink() ?>" target="_blank">IMDB</a>
                            |
                            <a href="<?php echo $selected->getTVDBLink() ?>" target="_blank">TheTVDB</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }
        ?>
        <a href="<?php echo $selected->getURLFetch() ?>" class="btn btn-primary" title="<?php pt('Fetches data, using the cache if available.'); ?>">
            <i class="glyphicon glyphicon-download"></i>
            <?php pt('Fetch data') ?>
        </a>
        <a href="<?php echo $selected->getURLClearAndFetch() ?>" class="btn btn-default" title="<?php pt('Clears the cache and fetches fresh data.') ?>">
            <i class="glyphicon glyphicon-download"></i>
            <?php echo htmlspecialchars(t('Clear and fetch')) ?>
        </a>
    </div>
    <div role="tabpanel" class="tab-pane" id="seasons">
        <?php
        $seasons = $selected->getSeasons();

        usort($seasons, static function(Season $a, Season $b) : int {
            return $b->getNumber() - $a->getNumber();
        });

        foreach($seasons as $season)
        {
            ?>
            <h4><?php pt('Season') ?> <?php echo $season->getNumber()  ?></h4>
            <table class="table">
                <tbody>
                <?php
                $episodes = $season->getEpisodes();

                usort($episodes, static function(Episode $a, Episode $b) : int{
                    return $b->getNumber() - $a->getNumber();
                });

                foreach($episodes as $episode)
                {
                    $links = array();
                    $urls = $episode->getSearchLinks();
                    foreach($urls as $def) {
                        $links[] = (string)sb()->link(
                            $def['label'],
                            $def['url'],
                            true
                        );
                    }

                    ?>
                    <tr>
                        <td style="text-align: center">
                            <?php
                            echo $episode->getDownloadStatusIcon();
                            ?>
                        </td>
                        <td style="text-align: right"><?php echo sprintf('%02d', $episode->getNumber()) ?></td>
                        <td><?php echo $episode->getSynopsis() ?></td>
                        <td style="white-space: nowrap"><?php echo implode(' | ', $links) ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
    <div role="tabpanel" class="tab-pane" id="settings">
        <?php
        (new SeriesForm($selected))->setTitleEnabled(false)->display();
        ?>
    </div>
</div>

