<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;
use Mistralys\SeriesManager\Manager\Library;
use function AppLocalize\pt;
use function AppLocalize\t;

$library = Library::createFromConfig();

Manager::setDocumentTitle(t('Series library'));

$tabs = array(
    Library::TAB_INDEX_STATUS => t('Index status'),
    Library::TAB_NAME_ALIASES => t('Name aliases'),
    Library::TAB_FILES_LIST => t('Files list'),
    Library::TAB_FOLDERS_LIST => t('Folders list')
);

$activeTabID = Library::DEFAULT_TAB;
if(isset($_REQUEST[Library::REQUEST_VAR_TAB], $tabs[$_REQUEST[Library::REQUEST_VAR_TAB]])) {
    $activeTabID = $_REQUEST[Library::REQUEST_VAR_TAB];
}

?>
<h3><?php pt('Series library'); ?></h3>
<br>
<ul class="nav nav-tabs">
    <?php
    foreach($tabs as $tabID => $label)
    {
        ?>
        <li class="<?php if($tabID === $activeTabID) {echo 'active'; } ?>">
            <a href="<?php echo $library->getURL(array(Library::REQUEST_VAR_TAB => $tabID)) ?>">
                <?php echo $label ?>
            </a>
        </li>
        <?php
    }
    ?>
</ul>
<br>
<?php

include __DIR__.'/library/'.$activeTabID.'.php';
