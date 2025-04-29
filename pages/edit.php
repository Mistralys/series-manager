<?php 

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use AppUtils\Request;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;

$request = Request::getInstance();
$manager = Manager::getInstance();
$selected = getEditSeries();
$activeTab = getActiveEditTab();

Manager::setDocumentTitle($selected->getName());

if($request->getBool('fetch'))
{
    $client = $manager->createClient();
    $selected->fetchData($client, $request->getBool('clear'), $request->getBool('dump'));
    $manager->getSeries()->save();

    header('Location:'.$selected->getURLEditTab(Series::EDIT_TAB_SEASONS));
}

function getEditSeries() : Series
{
    $manager = Manager::getInstance();
    $selected = $manager->getSelected();

    if($selected !== null) {
        return $selected;
    }

    die('No series selected.');
}

/**
 * @return array<string,string>
 */
function getEditTabs() : array
{
    return array(
        Series::EDIT_TAB_SUMMARY => t('Summary'),
        Series::EDIT_TAB_SEASONS => t('Seasons'),
        Series::EDIT_TAB_SETTINGS => t('Settings')
    );
}

function getActiveEditTab() : string
{
    return Request::getInstance()
        ->registerParam('tab')
        ->setEnum(array_keys(getEditTabs()))
        ->getString(Series::EDIT_TAB_SUMMARY);
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
    foreach(getEditTabs() as $tabID => $tabLabel) {
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

    <?php
        include __DIR__.'/edit/tab-summary.php';
        include __DIR__.'/edit/tab-seasons.php';
        include __DIR__.'/edit/tab-settings.php';
    ?>
</div>

