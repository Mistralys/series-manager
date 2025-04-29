<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Edit;

use Mistralys\SeriesManager\Series\Episode;
use Mistralys\SeriesManager\Series\Season;
use Mistralys\SeriesManager\Series\Series;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;
use function AppUtils\sb;
use function Mistralys\SeriesManager\Pages\getActiveEditTab;
use function Mistralys\SeriesManager\Pages\getEditSeries;

$selected = getEditSeries();
$activeTab = getActiveEditTab();

?>
<div
    role="tabpanel"
    class="tab-pane <?php

    if(Series::EDIT_TAB_SEASONS === $activeTab) { echo 'active'; } ?>"
    id="<?php echo Series::EDIT_TAB_SEASONS ?>"
>
    <a  href="<?php echo $selected->getURLClearAndFetch() ?>"
        class="btn btn-primary"
        title="<?php pt('Clears the cache and fetches fresh data.') ?>"
        data-toggle="tooltip"
    >
        <i class="glyphicon glyphicon-download"></i>
        <?php echo htmlspecialchars(t('Fetch data')) ?>
    </a>
    <a  href="<?php echo $selected->getURLClearAndFetch(array('dump' => 'yes')) ?>"
        class="btn btn-default"
        title="<?php pt('Fetches the data from the API and displays it.') ?>"
        data-toggle="tooltip"
    >
        <i class="glyphicon glyphicon-download"></i>
        <?php echo htmlspecialchars(t('Dump data')) ?>
    </a>
    <hr>
    <?php
    $seasons = $selected->getSeasons();

    usort($seasons, static function(Season $a, Season $b) : int {
        return $b->getNumber() - $a->getNumber();
    });

    foreach($seasons as $season)
    {
        ?>
        <h4><?php pt('Season') ?> <?php echo $season->getNumber()  ?></h4>
        <p>
            <?php

            pts('Entire season:');

            $links = array();
            foreach($season->getSearchLinks() as $def) {
                $links[] = (string)sb()->link(
                    $def['label'],
                    $def['url'],
                    true
                );
            }

            echo implode(' | ', $links);
            ?>
        </p>
        <table class="table">
            <tbody>
            <?php
            $episodes = $season->getEpisodes();

            usort($episodes, static function(Episode $a, Episode $b) : int{
                return $b->getNumber() - $a->getNumber();
            });

            foreach($episodes as $episode)
            {
                $links = array();
                $urls = $episode->getSearchLinks();
                foreach($urls as $def) {
                    $links[] = (string)sb()->link(
                        $def['label'],
                        $def['url'],
                        true
                    );
                }

                ?>
                <tr>
                    <td style="text-align: center">
                        <?php
                        echo $episode->getDownloadStatusIcon();
                        ?>
                    </td>
                    <td style="text-align: right"><?php echo sprintf('%02d', $episode->getNumber()) ?></td>
                    <td><?php echo $episode->getSynopsis() ?></td>
                    <td style="white-space: nowrap"><?php echo implode(' | ', $links) ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    }
    ?>
</div>


