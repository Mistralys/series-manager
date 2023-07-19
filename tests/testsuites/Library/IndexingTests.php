<?php

declare(strict_types=1);

namespace Mistralys\SeriesManagerTests\TestSuites\Library;

use Mistralys\SeriesManager\Manager\Library;
use Mistralys\SeriesManagerTests\Classes\SeriesManagerSuite;

final class IndexingTests extends SeriesManagerSuite
{
    public function test_normalizeNames() : void
    {
        $tests = array(
            array(
                'label' => 'Full name',
                'file' => 'game.of.thrones.s01e01.1080p.DDP5.H.264.EN-FR',
                'expected' => 'game of thrones',
                'season' => 1,
                'episode' => 1
            ),
            array(
                'label' => 'Shorthand season',
                'file' => 'game.of.thrones.S8E0.the.day.all.gods.die',
                'expected' => 'game of thrones',
                'season' => 8,
                'episode' => 0
            ),
            array(
                'label' => 'Spaces in season',
                'file' => 'game.of.thrones.S  01   E. 05  .the.day.all.gods.die',
                'expected' => 'game of thrones',
                'season' => 1,
                'episode' => 5
            ),
            array(
                'label' => 'Nothing after season',
                'file' => 'game.of.thrones.s06e42',
                'expected' => 'game of thrones',
                'season' => 6,
                'episode' => 42
            ),
            array(
                'label' => 'With apostrophes in name',
                'file' => 'dc\'s.legends.of.tomorrow.S02E02',
                'expected' => 'dcs legends of tomorrow',
                'season' => 2,
                'episode' => 2
            ),
            array(
                'label' => 'With year in parentheses before the episode number',
                'file' => 'Marvel\'s Agents of S.H.I.E.L.D. (2013) - S01E01 - Pilot',
                'expected' => 'marvels agents of shield',
                'season' => 1,
                'episode' => 1
            ),
            array(
                'label' => 'Doubled name',
                'file' => 'Game.of.Thrones.S01.1080p.WEB.DD5.1.H.264 - Game.of.Thrones.S01E01.Winter.Is.Coming.1080p.WEB.DD5.1.H.264',
                'expected' => 'game of thrones',
                'season' => 1,
                'episode' => 1
            )
        );

        $lib = new Library(array());

        foreach($tests as $test)
        {
            $result = $lib->parseName($test['file']);

            $this->assertNotNull($result, $test['label']);
            $this->assertSame($test['expected'], $result['name'], $test['label']);
            $this->assertSame($test['season'], $result['season'], $test['label']);
            $this->assertSame($test['episode'], $result['episode'], $test['label']);
        }
    }

    public function test_indexing() : void
    {
        $lib = $this->createTestLibrary();
        $files = $lib->getFiles();

        $this->assertCount(2, $files);
        $this->assertLibraryContainsFile($lib, 'game.of.thrones.s01e01.1080p.DDP5.H.264.EN-FR');
        $this->assertLibraryContainsFile($lib, 'Almas.Not.Normal.S01E05');
    }

    public function test_findEpisode() : void
    {
        $lib = $this->createTestLibrary();

        $file = $lib->findEpisode('Game Of Thrones', 1, 1);

        $this->assertNotNull($file);
        $this->assertSame('game.of.thrones.s01e01.1080p.DDP5.H.264.EN-FR', $file->getFile()->getBaseName());
    }

    // region: Support methods

    public function createTestLibrary() : Library
    {
        return new Library(array(
            __DIR__.'/../../files/library-1',
            __DIR__.'/../../files/library-2'
        ));
    }

    // endregion
}
