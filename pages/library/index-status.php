<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Library;

use AppUtils\ConvertHelper;
use Mistralys\SeriesManager\Manager\Library;
use function AppLocalize\pt;
use function AppLocalize\pts;

$library = Library::createFromConfig();
$cacheExists = $library->cacheExists();

if(isset($_REQUEST['clear-cache']) && $_REQUEST['clear-cache'] === 'yes')
{
    $library->clearCache();
    header('Location:'.$library->getURL());
}

if(isset($_REQUEST['create-index']) && $_REQUEST['create-index'] === 'yes')
{
    $library->createIndex();
    header('Location:'.$library->getURL());
}

?>
<p>
    <?php pt('Index last updated:') ?>
    <?php
    if($cacheExists)
    {
        echo ConvertHelper::date2listLabel($library->getCacheFile()->getModifiedDate(), true, true);
        ?>
        <br>
        <?php
        pts('Total files:');
        echo number_format(count($library->getFiles()), 0, '', ' ');
    }
    else
    {
        ?>
        <i class="text-muted"><?php pt('Never') ?></i>
        <?php
    }
    ?>
</p>
<p>
    <a href="<?php echo $library->getURLCreateIndex() ?>" class="btn btn-default">
        <?php
        if($cacheExists)
        {
            ?>
            <i class="glyphicon glyphicon-refresh"></i>
            <?php
            pt('Refresh index now');
        }
        else
        {
            ?>
            <i class="glyphicon glyphicon-floppy-save"></i>
            <?php
            pt('Build index now');
        }
        ?>
    </a>
</p>
