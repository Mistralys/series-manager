<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Library;

use Mistralys\SeriesManager\Manager\Library;
use Mistralys\SeriesManager\Manager\LibraryFile;
use function AppLocalize\pt;
use function AppLocalize\pts;

$library = Library::createFromConfig();
$files = $library->getFiles();

usort($files, static function(LibraryFile $a, LibraryFile $b) {
    return strnatcasecmp($a->getNameWithEpisode(), $b->getNameWithEpisode());
});

?>
<p>
    <?php
    pts('These are all video files that were found in the library folders.');
    pts('It also shows the series names that were automatically detected by the library.');
    ?>
</p>
<?php
if(!empty($files))
{
    ?>
    <table class="table">
        <thead>
        <tr>
            <th><?php pt('Auto-detected name'); ?></th>
            <th><?php pt('Episode') ?></th>
            <th><?php pt('Original file name') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($files as $file)
        {
            ?>
            <tr>
                <td style="white-space: nowrap"><?php echo $file->getName() ?></td>
                <td><?php echo $file->getEpisodeName() ?></td>
                <td><?php echo $file->getFile()->getName() ?></td>
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
        <?php pt('No files have been indexed yet, or there are no files in the library folders.'); ?>
    </div>
    <?php
}