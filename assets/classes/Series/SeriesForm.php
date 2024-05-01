<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\OutputBuffering;
use AppUtils\Request;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\SeriesCollection;
use function AppLocalize\pt;

class SeriesForm
{
    public const SETTING_IMDB_ID = 'imdbid';
    public const SETTING_TVDB_ALIAS = 'tvdbalias';
    public const SETTING_TVDB_ID = 'tvdbid';
    public const SETTING_NAME = 'name';
    public const REQUEST_PARAM_NAME_SEARCH = 'name-search';

    private ?Series $series;
    private SeriesCollection $collection;
    private string $title;

    /**
     * @var array<string,string>
     */
    private array $values = array(
        self::SETTING_NAME => '',
        self::SETTING_TVDB_ID => '',
        self::SETTING_IMDB_ID => '',
        self::SETTING_TVDB_ALIAS => ''
    );
    private bool $titleEnabled = true;

    public function __construct(?Series $series)
    {
        $this->series = $series;
        $this->collection = Manager::getInstance()->getSeries();
        $this->title = 'Add a series';

        $this->loadDefaults();
        $this->handle();
    }

    private function loadDefaults() : void
    {
        if (!isset($this->series))
        {
            $request = Request::getInstance();

            $this->values[self::SETTING_NAME] = $request->getFilteredParam(self::REQUEST_PARAM_NAME_SEARCH);
            return;
        }

        $this->title = 'Settings';

        $this->values[self::SETTING_NAME] = $this->series->getName();
        $this->values[self::SETTING_IMDB_ID] = $this->series->getIMDBID();
        $this->values[self::SETTING_TVDB_ID] = $this->series->getTVDBID();
        $this->values[self::SETTING_TVDB_ALIAS] = $this->series->getTVDBAlias();
    }

    private function handle() : void
    {
        if (!isset($_REQUEST['save']) || $_REQUEST['save'] !== 'yes')
        {
            return;
        }

        $name = $this->cleanRequestVar(self::SETTING_NAME);
        $alias = $this->cleanRequestVar(self::SETTING_TVDB_ALIAS);
        $imdbID = $this->cleanRequestVar(self::SETTING_IMDB_ID);
        $tvdbID = $this->cleanRequestVar(self::SETTING_TVDB_ID);

        if (isset($this->series))
        {
            $series = $this->series;

            $this->series->setName($name);
            $this->series->setTVDBAlias($alias);
            $this->series->setIMDBID($imdbID);
            $this->series->setTVDBID($tvdbID);
        }
        else
        {
            $series = $this->collection->add(array(
                Series::KEY_NAME => $name,
                Series::KEY_TVDB_ALIAS => $alias,
                Series::KEY_IMDB_ID => $imdbID,
                Series::KEY_TVDB_ID => $tvdbID
            ));
        }

        $this->collection->save();

        header('Location:'.$series->getURLEditTab(Series::EDIT_TAB_SETTINGS));
        exit;
    }

    public function display() : void
    {
        echo $this->render();
    }

    private function cleanRequestVar(string $name) : ?string
    {
        if(!isset($_REQUEST[$name])) {
            return null;
        }

        return trim(htmlspecialchars(strip_tags($_REQUEST[$name]), ENT_QUOTES, 'UTF-8'));
    }
    public function render() : string
    {
        OutputBuffering::start();
        ?>
        <script src="js/add.js"></script>
        <script>
            $('document').ready(function () {
                <?php
                if(!isset($this->series))
                {
                    ?>
                    $('#f-<?php echo self::SETTING_NAME ?>').focus();
                    <?php
                }
                ?>

                UpdateSearchLinks();
            });
        </script>
        <?php
        if($this->titleEnabled) {
            ?>
            <h3><?php echo $this->title ?></h3>
            <?php
        }
        ?>
        <form method="post">
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_NAME ?>"><?php pt('Name') ?>*</label>
                <input type="text" name="<?php echo self::SETTING_NAME ?>" class="form-control"
                       id="f-<?php echo self::SETTING_NAME ?>" placeholder="Game of Thrones"
                       onkeyup="UpdateSearchLinks()" value="<?php echo $this->values[self::SETTING_NAME] ?>"/>
                <p class="help-block" id="searchlinks"></p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_IMDB_ID ?>"><?php pt('%1$s ID', 'IMDB') ?>*</label>
                <input type="text" name="<?php echo self::SETTING_IMDB_ID ?>" class="form-control"
                       id="f-<?php echo self::SETTING_IMDB_ID ?>" placeholder="tt12345678"
                       value="<?php echo $this->values[self::SETTING_IMDB_ID] ?>"/>
                <p class="help-block">
                    https://imdb.com/title/<span style="color:#cc0000">tt2234222</span>/
                </p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_TVDB_ID ?>"><?php pt('%1$s ID', 'TVDB') ?></label>
                <input type="text" name="<?php echo self::SETTING_TVDB_ID ?>" class="form-control"
                       id="f-<?php echo self::SETTING_TVDB_ID ?>" placeholder="123456"
                       value="<?php echo $this->values[self::SETTING_TVDB_ID] ?>"/>
                <p class="help-block">
                    <?php pt('Shown on the detail page of the series.') ?>
                </p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_TVDB_ALIAS ?>"><?php pt('%1$s Alias', 'TVDB') ?></label>
                <input type="text" name="<?php echo self::SETTING_TVDB_ALIAS ?>" class="form-control"
                       id="f-<?php echo self::SETTING_TVDB_ALIAS ?>" placeholder="game-of-thrones"
                       value="<?php echo $this->values[self::SETTING_TVDB_ALIAS] ?>"/>
                <p class="help-block">
                    https://thetvdb.com/series/<span style="color:#cc0000">game-of-thrones</span>
                </p>
            </div>
            <input type="hidden" name="save" value="yes"/>

            <?php
            if (isset($this->series))
            {
                ?>
                <input type="hidden" name="id" value="<?php echo $this->series->getIMDBID() ?>"/>
                <input type="hidden" name="tab" value="<?php echo Series::EDIT_TAB_SETTINGS ?>"/>
                <button type="submit" class="btn btn-primary">
                    <i class="glyphicon glyphicon-save"></i>
                    <?php pt('Save now'); ?>
                </button>
                <a href="./" class="btn btn-default">
                    <?php pt('Cancel') ?>
                </a>
                <?php
            }
            else
            {
                ?>
                <button type="submit" class="btn btn-primary">
                    <i class="glyphicon glyphicon-plus"></i>
                    <?php pt('Add series') ?>
                </button>
                <a href="./" class="btn btn-default">
                    <?php pt('Cancel') ?>
                </a>
                <?php
            }
            ?>
        </form>
        <?php
        return OutputBuffering::get();
    }

    public function setTitleEnabled(bool $enabled) : self
    {
        $this->titleEnabled = $enabled;
        return $this;
    }
}
