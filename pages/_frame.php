<?php

declare(strict_types=1);

use AppLocalize\Localization;
use Mistralys\SeriesManager\Manager;
use function AppLocalize\pt;

$manager = Manager::getInstance();

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Manager::getDocumentTitle() ?></title>
    <?php
    if(Manager::isDarkMode())
    {
        ?>
        <link href="css/bootswatch.min.css" rel="stylesheet">
        <?php
    }
    else
    {
        ?>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <?php
    }
    ?>
    <link href="css/main.css" rel="stylesheet">
    <script src="js/jquery-1.11.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function()
        {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
  </head>
  <body class="<?php if(Manager::isDarkMode()) { echo 'dark-mode'; } ?>">
    <nav class="navbar navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="./"><?php echo Manager::getName() ?></a>
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
            <?php echo Manager::getName() ?>
            <a href="https://github.com/Mistralys/series-manager/releases">
                v<?php echo $manager->getVersion() ?>
            </a>
            |
            <?php pt('Interface language:') ?>
            <?php
            $locales = Localization::getAppLocales();
            foreach($locales as $locale)
            {
                if($locale->getName() === Localization::getAppLocale()->getName())
                {
                    ?>
                    <strong><?php echo strtoupper($locale->getLanguageCode()) ?></strong>
                    <?php
                }
                else
                {
                    ?>
                    <a href="?selectLocale=<?php echo $locale->getName() ?>" title="<?php echo $locale->getLabel() ?>">
                        <?php echo strtoupper($locale->getLanguageCode()) ?>
                    </a>
                    <?php
                }
            }
            ?>
            |
            <a href="?toggleDarkMode=yes"><?php pt('Toggle dark mode') ?></a>
        </p>
        <br>
        <br> 
    </div>
  </body>
</html>