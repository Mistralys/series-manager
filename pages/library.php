<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use AppUtils\ConvertHelper;
use Mistralys\SeriesManager\Manager\Library;
use Mistralys\SeriesManager\Manager\LibraryFile;

$library = Library::createFromConfig();

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

$files = $library->getFiles();

usort($files, static function(LibraryFile $a, LibraryFile $b) {
    return strnatcasecmp($a->getNameWithEpisode(), $b->getNameWithEpisode());
});

$cacheExists = $library->cacheExists();

?>
<h3>Series library</h3>
<p>
    Index last updated:
    <?php
    if($cacheExists)
    {
        echo ConvertHelper::date2listLabel($library->getCacheFile()->getModifiedDate(), true, true);
        ?>
        <br>
        Total files: <?php echo number_format(count($files), 0, '', ' ') ?>
        <?php
    }
    else
    {
        ?>
        <i class="text-muted">Never</i>
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
            Refresh index now
            <?php
        }
        else
        {
            ?>
            <i class="glyphicon glyphicon-floppy-save"></i>
            Build index now
            <?php
        }
        ?>
    </a>
</p>
<?php

if($cacheExists)
{
    ?>
    <hr>
    <h4>Files list</h4>
    <table class="table">
        <tbody>
        <?php
        foreach($files as $file)
        {
            ?>
            <tr>
                <td style="white-space: nowrap"><?php echo $file->getNameWithEpisode() ?></td>
                <td><?php echo $file->getFile()->getName() ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
}