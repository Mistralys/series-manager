<?php

declare(strict_types=1);

use Mistralys\SeriesManager\Manager;

$manager = Manager::getInstance();

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Series</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery-1.11.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </head>
  <body>
    <nav class="navbar navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="./">Series Manager</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <?php
                $pages = $manager->getPages();
                $activePageID = $manager->getPageID(); 
                foreach($pages as $pageID => $pageLabel) {
                    $active = '';
                    if($pageID===$activePageID) {
                        $active = ' class="active"';
                    }
                    
                    echo 
                    '<li'.$active.'>'.
                        '<a href="?page='.$pageID.'">'.
                            $pageLabel.
                        '</a>'.
                    '</li>';
                }
            ?>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
        <?php echo $manager->getContent(); ?>
        <br>
        <br>
        <hr>
        <p>
            Series Manager v<?php echo $manager->getVersion() ?>
        </p>
        <br>
        <br> 
        
    </div>
  </body>
</html>