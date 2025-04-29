<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Edit;

use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;
use function Mistralys\SeriesManager\Pages\getActiveEditTab;
use function Mistralys\SeriesManager\Pages\getEditSeries;

$selected = getEditSeries();
$activeTab = getActiveEditTab();

?>
<div
    role="tabpanel"
    class="tab-pane <?php

    if(Series::EDIT_TAB_SUMMARY === $activeTab) { echo 'active'; } ?>"
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
