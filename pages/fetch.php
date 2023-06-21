<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

$manager = Manager::getInstance();
$series = $manager->getSeries();

if(isset($_REQUEST['confirm'])) {
    $clearCache = false;
    if(isset($_REQUEST['clear_cache']) && $_REQUEST['clear_cache'] === 'yes') {
        $clearCache = true;
    }
    
    $messages = $series->fetchData($clearCache);
    $series->save();
    
    echo 
    '<h3>Fetch online data</h3>'.
    '<p>Fetch results:</p>'.
    '<ul class="unstyled">'.
        '<li>'.implode('</li><li>', $messages).'</li>'.
    '</ul>';
   
    return;
}

?>
<h3>Fetch online data</h3>
<p>Note: this only works if TheTVDB ID is set. Series that do not have it will be skipped.</p>
<form method="post">
    <div class="checkbox">
        <label>
            <input type="checkbox" name="clear_cache" value="yes"> Clear cache
        </label>
    </div>
    <input type="hidden" name="page" value="fetch"/>
    <input type="hidden" name="confirm" value="yes"/>
    <button type="submit" class="btn btn-primary">
        <i class="glyphicon glyphicon-flash"></i>
        Fetch now
    </button>
</form>