<?php

declare(strict_types=1);

namespace Mistralys\SeriesManagerTests\Classes;

use Mistralys\SeriesManager\Manager\Library;
use PHPUnit\Framework\TestCase;

abstract class SeriesManagerSuite extends TestCase
{
    public function assertLibraryContainsFile(Library $library, string $fileName) : void
    {
        $files = $library->getFiles();

        foreach($files as $file)
        {
            if($file->getFile()->getBaseName() === $fileName) {
                return;
            }
        }

        $this->fail(sprintf(
            'The file [%s] was not found in the library.'.PHP_EOL.
            'Available files: [%s].',
            $fileName,
            count($files)
        ));
    }
}
