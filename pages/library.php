<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager\Library;
use function AppLocalize\pt;
use function AppLocalize\t;

$library = Library::createFromConfig();

$tabs = array(
    Library::TAB_INDEX_STATUS => t('Index status'),
    Library::TAB_NAME_ALIASES => t('Name aliases'),
    Library::TAB_FILES_LIST => t('Files list')
);

$activeTabID = Library::DEFAULT_TAB;
if(isset($_REQUEST['tab'], $tabs[$_REQUEST['tab']])) {
    $activeTabID = $_REQUEST['tab'];
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
            <a href="<?php echo $library->getURL(array('tab' => $tabID)) ?>">
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
