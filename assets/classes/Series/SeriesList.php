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
            <tr <?php if($item->isFavorite()) { echo ' class="warning"'; } ?>>
                <td>
                    <a href="<?php echo $item->getURLEdit() ?>"><?php echo $item->getName() ?></a>
                    <?php
                    if($item->isFavorite())
                    {
                        ?>
                        <a href="<?php echo $item->getURLUnfavorite() ?>" style="color:#ffdc00">
                            <i class="glyphicon glyphicon-star"></i>
                        </a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a href="<?php echo $item->getURLFavorite() ?>" class="text-muted">
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
                        <a href="<?php echo $item->getURLUnarchive() ?>" class="btn btn-default">
                            <i class="glyphicon glyphicon-bookmark"></i>
                        </a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a href="<?php echo $item->getURLArchive() ?>" class="btn btn-default">
                            <i class="glyphicon glyphicon-bookmark"></i>
                        </a>
                        <?php
                    }
                    ?>
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
    <?php
        return OutputBuffering::get();
    }
}