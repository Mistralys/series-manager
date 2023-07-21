<?php
/**
 * Translation UI for the localizable strings in the package.
 *
 * @package Application Utils
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

use AppLocalize\Localization;
use Mistralys\SeriesManager\Manager;
use function AppLocalize\t;

$autoload = __DIR__.'/../vendor/autoload.php';

// we need the autoloader to be present
if(!file_exists($autoload))
{
    die('<b>ERROR:</b> Autoloader not present. Run composer install first.');
}

/**
 * The composer autoloader
 */
require_once $autoload;

$installFolder = __DIR__.'/../';

Manager::initLocalization();

// create the editor UI and start it
$editor = Localization::createEditor();

$editor->setAppName('Series Manager Translation');

$editor->display();
