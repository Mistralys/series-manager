<?php 

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use AppUtils\Request;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Episode;
use Mistralys\SeriesManager\Series\Season;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\Series\SeriesForm;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;
use function AppUtils\sb;

$editTabs = array(
    Series::EDIT_TAB_SUMMARY => t('Summary'),
    Series::EDIT_TAB_SEASONS => t('Seasons'),
    Series::EDIT_TAB_SETTINGS => t('Settings')
);

$request = Request::getInstance();
$manager = Manager::getInstance();
$selected = $manager->getSelected();

$activeTab = $request
    ->registerParam('tab')
    ->setEnum(array_keys($editTabs))
    ->getString(Series::EDIT_TAB_SUMMARY);

if($selected === null) {
    die('No series selected.');
}

$form = (new SeriesForm($selected))
    ->setTitleEnabled(false);

if($request->getBool('fetch'))
{
    $client = $manager->createClient();
    $selected->fetchData($client, $request->getBool('clear'), $request->getBool('dump'));
    $manager->getSeries()->save();

    header('Location:'.$selected->getURLEditTab(Series::EDIT_TAB_SEASONS));
}

?>
<h3>
    <?php echo $selected->getName(); ?>
    <?php echo $selected->renderFavoriteIcon() ?>
    <?php
    if($selected->isArchived()) {
        ?>
        <i class="text-muted" style="font-size: 50%;vertical-align: middle">(<?php pt('Archived') ?>)</i>
        <?php
    }
    ?>
</h3>
<ul class="nav nav-tabs" role="tablist">
    <?php
    foreach($editTabs as $tabID => $tabLabel) {
        ?>
        <li role="presentation" <?php if($tabID === $activeTab) { echo 'class="active"'; } ?>>
            <a href="#<?php echo $tabID ?>" aria-controls="<?php echo $tabID ?>" role="tab" data-toggle="tab">
                <?php echo $tabLabel?>
            </a>
        </li>
        <?php
    }
    ?>
</ul>
<div class="tab-content">
    <div
        role="tabpanel"
        class="tab-pane <?php if(Series::EDIT_TAB_SUMMARY === $activeTab) { echo 'active'; } ?>"
        id="<?php echo Series::EDIT_TAB_SUMMARY ?>"
    >
        <?php

        if($selected->hasInfo())
        {
            ?>
            <table class="table table-properties">
                <tbody>
                    <tr>
                        <th><?php pt('Status') ?></th>
                        <td><?php echo $selected->getStatus() ?></td>
                    </tr>
                    <tr>
                        <th><?php pt('Synopsis') ?></th>
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
                        <th><?php pt('%1$s ID', 'IMDB') ?></th>
                        <td>
                            <a href="<?php echo $selected->getIMDBLink() ?>" target="_blank">
                                <?php echo $selected->getIMDBID() ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th><?php pt('%1$s ID', 'TheTVDB') ?></th>
                        <td>
                            <a href="<?php echo $selected->getTVDBLink() ?>" target="_blank">
                                <?php echo $selected->getTVDBID() ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        if($selected->isArchived())
        {
            ?>
            <a  href="<?php echo $selected->getURLUnarchive(null, 'edit') ?>"
                class="btn btn-default"
                data-toggle="tooltip"
                title="<?php pts('Removes the series from the archives.'); pts('It will be shown in the %1$s again.', t('Overview')); ?>"
            >
                <i class="glyphicon glyphicon-circle-arrow-up"></i>
                <?php pt('Remove from archive') ?>
            </a>
            <?php
        }
        else
        {
            ?>
            <a  href="<?php echo $selected->getURLArchive(null, 'edit') ?>"
                class="btn btn-default"
                data-toggle="tooltip"
                title="<?php pts('Marks the series as archived.'); pts('It will still be visible in the %1$s screen.', t('Archive')); ?>"
            >
                <i class="glyphicon glyphicon-book"></i>
                <?php pt('Send to archive') ?>
            </a>
            <?php
        }

        if($selected->isFavorite())
        {
            ?>
            <a  href="<?php echo $selected->getURLUnfavorite(null, 'edit') ?>"
                class="btn btn-default"
                data-toggle="tooltip"
                title="<?php pts('Removes the favorite flag from the series.') ?>"
            >
                <i class="glyphicon glyphicon-star"></i>
                <?php pt('Remove favorite') ?>
            </a>
            <?php
        }
        else
        {
            ?>
            <a  href="<?php echo $selected->getURLFavorite(null, 'edit') ?>"
                class="btn btn-default"
                data-toggle="tooltip"
                title="<?php pts('Marks the series as a favorite.') ?>"
            >
                <i class="glyphicon glyphicon-star-empty"></i>
                <?php pt('Make favorite') ?>
            </a>
            <?php
        }
        ?>
        &#160;
        <a  href="<?php echo $selected->getURLDelete() ?>"
            style="float: right"
            class="btn btn-danger"
            data-toggle="tooltip"
            title="<?php pts('Deletes the series.'); pts('Leaves files on disk unchanged.'); ?>"
        >
            <i class="glyphicon glyphicon-remove-sign"></i>
            <?php pt('Delete') ?>
        </a>
    </div>
    <div
        role="tabpanel"
        class="tab-pane <?php if(Series::EDIT_TAB_SEASONS === $activeTab) { echo 'active'; } ?>"
        id="<?php echo Series::EDIT_TAB_SEASONS ?>"
    >
        <a  href="<?php echo $selected->getURLClearAndFetch() ?>"
            class="btn btn-primary"
            title="<?php pt('Clears the cache and fetches fresh data.') ?>"
            data-toggle="tooltip"
        >
            <i class="glyphicon glyphicon-download"></i>
            <?php echo htmlspecialchars(t('Fetch data')) ?>
        </a>
        <a  href="<?php echo $selected->getURLClearAndFetch(array('dump' => 'yes')) ?>"
            class="btn btn-default"
            title="<?php pt('Fetches the data from the API and displays it.') ?>"
            data-toggle="tooltip"
        >
            <i class="glyphicon glyphicon-download"></i>
            <?php echo htmlspecialchars(t('Dump data')) ?>
        </a>
        <hr>
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
    <div
        role="tabpanel"
        class="tab-pane <?php if(Series::EDIT_TAB_SETTINGS === $activeTab) { echo 'active'; } ?>"
        id="<?php echo Series::EDIT_TAB_SETTINGS ?>"
    >
        <?php
        $form->display();
        ?>
    </div>
</div>

