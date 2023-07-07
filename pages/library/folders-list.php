<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Library;

use AppUtils\ConvertHelper;
use AppUtils\Request;
use Mistralys\SeriesManager\Manager\Library;
use function AppLocalize\pt;
use function AppLocalize\pts;

$request = Request::getInstance();
$library = Library::createFromConfig();
$folders = $library->getAvailableFolders(
    (string)$request->getParam('sortBy', ''),
    (string)$request->getParam('sortDir', 'asc')
);

?>
    <p>
        <?php
        pts('These are all folders that were found in the configured library folders.');
        pts('It can be used to find series that are present on disk, but have not been added yet.');
        ?>
    </p>
<?php
if(!empty($folders))
{
    ?>
    <p>
        <?php pt('Found %1$s folders.', count($folders)) ?>
    </p>
    <table class="table">
        <thead>
        <tr>
            <th>
                <?php pt('Name'); ?>
                <a href="?page=library&tab=folders-list&sortBy=name&sortDir=asc">^</a>
                <a href="?page=library&tab=folders-list&sortBy=name&sortDir=desc">v</a>
            </th>
            <th style="text-align: center;">
                <?php pt('Added?') ?>
            </th>
            <th style="text-align: right">
                <?php pt('Created') ?>
                <a href="?page=library&tab=folders-list&sortBy=date&sortDir=asc">^</a>
                <a href="?page=library&tab=folders-list&sortBy=date&sortDir=desc">v</a>
            </th>
            <th><?php pt('Library folder'); ?></th>
            <th><?php pt('Actions'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($folders as $folder)
        {
            ?>
            <tr>
                <td style="white-space: nowrap"><?php echo $folder->getNameLinked() ?></td>
                <td style="text-align: center">
                    <?php
                    if($folder->exists())
                    {
                        ?>
                        <i class="glyphicon glyphicon-ok-circle text-success"></i>
                        <?php
                    }
                    else
                    {
                        ?>
                        <i class="glyphicon glyphicon-ban-circle text-danger"></i>
                        <?php
                    }
                    ?>
                </td>
                <td style="text-align: right"><?php echo ConvertHelper::date2listLabel($folder->getDate()) ?></td>
                <td><?php echo $folder->getLibraryFolderName() ?></td>
                <td>
                    <?php
                    if(!$folder->exists())
                    {
                        ?>
                        <a href="<?php echo $folder->getURLAdd() ?>" class="btn btn-default btn-xs">
                            <i class="glyphicon glyphicon-plus-sign"></i>
                            <?php pt('Add...') ?>
                        </a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
}
else
{
    ?>
    <div class="alert alert-info">
        <?php pt('No folders have been indexed yet, or there are no folders in the library folders.'); ?>
    </div>
    <?php
}