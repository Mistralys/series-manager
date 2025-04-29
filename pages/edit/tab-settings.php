<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Edit;

use Mistralys\SeriesManager\Series\Series;
use Mistralys\SeriesManager\Series\SeriesForm;
use function Mistralys\SeriesManager\Pages\getActiveEditTab;
use function Mistralys\SeriesManager\Pages\getEditSeries;

$selected = getEditSeries();
$activeTab = getActiveEditTab();

$form = (new SeriesForm($selected))
    ->setTitleEnabled(false);

?>
<div
    role="tabpanel"
    class="tab-pane <?php

    if(Series::EDIT_TAB_SETTINGS === $activeTab) { echo 'active'; } ?>"
    id="<?php echo Series::EDIT_TAB_SETTINGS ?>"
>
    <?php
    $form->display();
    ?>
</div>
