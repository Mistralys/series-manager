<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\JSHelper;
use AppUtils\OutputBuffering;
use AppUtils\Request;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\SeriesCollection;
use Mistralys\SeriesManager\UI;
use function AppLocalize\pt;
use function AppLocalize\ptex;
use function AppLocalize\pts;
use function AppLocalize\t;

class SeriesList
{
    private SeriesCollection $collection;
    private bool $archived = false;
    private Request $request;

    public function __construct()
    {
        $this->collection = Manager::getInstance()->getSeries();
        $this->request = Request::getInstance();
    }

    /**
     * @return Series[]
     */
    public function getSeries() : array
    {
        $items = $this->collection->getAll();
        $keep = array();

        foreach($items as $item)
        {
            if($item->isArchived() === $this->archived)
            {
                $keep[] = $item;
            }
        }

        return $keep;
    }

    public function display() : void
    {
        echo $this->render();
    }

    public function selectArchived(bool $archived) : self
    {
        $this->archived = $archived;
        return $this;
    }

    public function getTitle() : string
    {
        if($this->archived) {
            return t('Archived series');
        }

        return t('Available series');
    }

    private function handleActions() : void
    {
        if(!$this->request->getBool('update')) {
            return;
        }

        $series = $this->request->getParam('series');
        if(!is_array($series)) {
            return;
        }

        foreach($series as $imdbID => $data)
        {
            $item = $this->collection->getByIMDBID($imdbID);
            $item->setLastDLSeason($data['lastDLSeason']);
            $item->setLastDLEpisode($data['lastDLEpisode']);
        }

        $this->collection->save();

        $url = Manager::getInstance()->getURL(array('page' => (string)$this->request->getParam('page')));
        header('Location:'.$url);
        exit;
    }

    public function render() : string
    {
        Manager::setDocumentTitle(t('Series overview'));

        $this->handleActions();

        OutputBuffering::start();

        $items = $this->getSeries();
?>
<h3><?php echo $this->getTitle() ?></h3>
<?php

        if(empty($items)) {
            ?>
            <div class="alert alert-info">
                <?php pt('No series found.') ?>
            </div>
            <?php
            return OutputBuffering::get();
        }

?>
<script src="js/list.js"></script>
<form class="form-inline" onsubmit="return false;">
    <div class="form-group">
        <input type="search" class="form-control" id="list-filter" placeholder="<?php pt('Filter list...') ?>" onkeyup="filterList()">
    </div>
    <div class="checkbox" style="float: right">
        <label title="<?php pt('Only displays favorite series.') ?>" data-toggle="tooltip">
            <input  type="checkbox"
                    id="list-favorites"
                    onchange="filterList()">
            <?php pt('Favorites only') ?>
        </label>
    </div>
    <button type="button" class="btn btn-default" onclick="$('#list-filter').val('');filterList();this.blur()">
        <i class="glyphicon glyphicon-remove"></i>
    </button>
</form>
<small class="text-muted"><?php pt('Searches in the name, TVDB ID and IMDB ID.') ?></small>
<hr>
<form method="post" class="form-inline">
    <table class="table table-hover" id="series-list">
        <thead>
        <tr>
            <td><?php pt('Name') ?></td>
            <td><?php pt('Status') ?></td>
            <td style="text-align: right"><?php pt('Seasons') ?></td>
            <td style="text-align: right"><?php pt('Episodes') ?></td>
            <td style="text-align: center"><?php pt('Complete?') ?></td>
            <td><?php pt('Links') ?></td>
            <td><?php pt('Last watched') ?></td>
            <td></td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($items as $item)
        {
            $rowID = JSHelper::nextElementID();

            ?>
            <tr class="list-row <?php if($item->isFavorite()) { echo ' warning favorite'; } ?>" data-search-text="<?php echo htmlspecialchars($item->getSearchText(), ENT_QUOTES) ?>">
                <td>
                    <a href="<?php echo $item->getURLEdit() ?>"><?php echo $item->getName() ?></a>
                    <?php
                    if($item->isFavorite())
                    {
                        ?>
                        <a  href="<?php echo $item->getURLUnfavorite() ?>"
                            style="color:#ffdc00"
                            data-toggle="tooltip"
                            title="<?php pts('Removes the favorite flag from the series.') ?>"
                        >
                            <i class="glyphicon glyphicon-star"></i>
                        </a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a  href="<?php echo $item->getURLFavorite() ?>"
                            class="text-muted"
                            data-toggle="tooltip"
                            title="<?php pts('Marks the series as a favorite.') ?>"
                        >
                            <i class="glyphicon glyphicon-star-empty"></i>
                        </a>
                        <?php
                    }
                    ?>
                </td>
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
                    <?php
                    if($item->isArchived())
                    {
                        ?>
                        <a  href="<?php echo $item->getURLUnarchive() ?>"
                            class="btn btn-default"
                            data-toggle="tooltip"
                            title="<?php pts('Unarchives the series.'); pts('It will be shown in the %1$s again.', t('Overview')); ?>"
                        >
                            <i class="glyphicon glyphicon-circle-arrow-up"></i>
                        </a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a  href="<?php echo $item->getURLArchive() ?>"
                            class="btn btn-default"
                            data-toggle="tooltip"
                            title="<?php pts('Marks the series as archived.'); pts('It will still be visible in the %1$s screen.', t('Archive')); ?>"
                        >
                            <i class="glyphicon glyphicon-book"></i>
                        </a>
                        <?php
                    }
                    ?>
                    <a  href="<?php echo $item->getURLDelete() ?>"
                        class="btn btn-danger"
                        data-toggle="tooltip"
                        title="<?php pts('Deletes the series.'); pts('Leaves files on disk unchanged.'); ?>"
                    >
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
    <?php
        return OutputBuffering::get();
    }
}
