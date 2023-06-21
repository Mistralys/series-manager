<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Series;

use AppUtils\OutputBuffering;
use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\SeriesCollection;

class SeriesForm
{
    public const SETTING_IMDB_ID = 'imdbid';
    public const SETTING_TVDB_ALIAS = 'tvdbalias';
    public const SETTING_TVDB_ID = 'tvdbid';
    public const SETTING_NAME = 'name';

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
            $this->series->setName($name);
            $this->series->setTVDBAlias($alias);
            $this->series->setIMDBID($imdbID);
            $this->series->setTVDBID($tvdbID);
        }
        else
        {
            $this->collection->add(array(
                Series::KEY_NAME => $name,
                Series::KEY_TVDB_ALIAS => $alias,
                Series::KEY_IMDB_ID => $imdbID,
                Series::KEY_TVDB_ID => $tvdbID
            ));
        }

        $this->collection->save();

        header('Location:./');
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
                $('#f-<?php echo self::SETTING_NAME ?>').focus();
            });
        </script>
        <h3><?php echo $this->title ?></h3>
        <form method="post">
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_NAME ?>">Name</label>
                <input type="text" name="<?php echo self::SETTING_NAME ?>" class="form-control"
                       id="f-<?php echo self::SETTING_NAME ?>" placeholder="Game of Thrones"
                       onkeyup="UpdateSearchLinks()" value="<?php echo $this->values[self::SETTING_NAME] ?>"/>
                <p class="help-block" id="searchlinks"></p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_IMDB_ID ?>">IMDB ID</label>
                <input type="text" name="<?php echo self::SETTING_IMDB_ID ?>" class="form-control"
                       id="f-<?php echo self::SETTING_IMDB_ID ?>" placeholder="tt12345678"
                       value="<?php echo $this->values[self::SETTING_IMDB_ID] ?>"/>
                <p class="help-block">
                    https://imdb.com/title/<span style="color:#cc0000">tt2234222</span>/
                </p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_TVDB_ID ?>">TVDB ID</label>
                <input type="text" name="<?php echo self::SETTING_TVDB_ID ?>" class="form-control"
                       id="f-<?php echo self::SETTING_TVDB_ID ?>" placeholder="123456"
                       value="<?php echo $this->values[self::SETTING_TVDB_ID] ?>"/>
                <p class="help-block">
                    Shown on the detail page of the series.
                </p>
            </div>
            <div class="form-group">
                <label for="f-<?php echo self::SETTING_TVDB_ALIAS ?>">TVDB Alias</label>
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
                <button type="submit" class="btn btn-primary">
                    <i class="glyphicon glyphicon-save"></i>
                    Save now
                </button>
                <a href="./" class="btn btn-default">
                    Cancel
                </a>
                <?php
            }
            else
            {
                ?>
                <button type="submit" class="btn btn-primary">
                    <i class="glyphicon glyphicon-plus"></i>
                    Add series
                </button>
                <a href="./" class="btn btn-default">
                    Cancel
                </a>
                <?php
            }
            ?>
        </form>
        <?php
        return OutputBuffering::get();
    }
}
