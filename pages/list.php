<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\Manager;

$manager = Manager::getInstance();
$series = $manager->getSeries();

if(isset($_REQUEST['update'], $_REQUEST['series']) && $_REQUEST['update'] === 'yes') {
    foreach($_REQUEST['series'] as $rarbgID => $data) {
        $item = $series->getByIMDBID($rarbgID);
        $item->setLastDLSeason($data['lastDLSeason']);
        $item->setLastDLEpisode($data['lastDLEpisode']);
    }

    $series->save();
    header('Location:./');
    exit;
}

$items = $series->getAll();

$activeID = $manager->getSelectedID();

$html = '';
if(empty($items)) {
    echo
    '<div class="alert alert-info">'.
        'No series found.'.
    '</div>';
    
    return;
} 

$html .=
'<h3>Available series</h3>'.
'<form method="post" class="form-inline">'.
    '<table class="table table-hover">'.
        '<thead>'.
            '<tr>'.
                '<td>Name</td>'.
                '<td>Status</td>'.
                '<td>Seasons</td>'.
                '<td>Episodes</td>'.
                '<td>Links</td>'.
                '<td>Last Downloaded</td>'.
                '<td></td>'.
            '</tr>'.
        '</thead>'.
        '<tbody>';
            foreach($items as $item) {
                $html .=
                '<tr>'.
                    '<td><a href="?id='.$item->getIMDBID().'">'.$item->getName().'</a></td>'.
                    '<td>'.$item->getStatus().'</td>'.
                    '<td>'.$item->countSeasons().'</td>'.
                    '<td>'.$item->countEpisodes().'</td>'.
                    '<td>';
                        $links = $item->getLinks();
                        $tokens = array();
                        foreach($links as $link) {
                            $tokens[] = 
                            '<a href="'.$link['url'].'" target="_blank">'.
                                $link['label'].
                            '</a>';
                        }
                        
                        $html .= 
                        implode(' | ', $tokens).
                    '</td>'.
                    '<td>'.
                        '<div class="form-group">'.
                            '<div class="input-group">'.
                                '<div class="input-group-addon">'.
                                    'S'.
                                '</div>'.
                                '<input name="series['.$item->getIMDBID().'][lastDLSeason]" type="number" class="form-control" value="'.$item->getLastDLSeason().'" style="width:60px"/>'.
                            '</div> '.
                            '<div class="input-group">'.
                                '<div class="input-group-addon">'.
                                    'E'.
                                '</div>'.
                                '<input name="series['.$item->getIMDBID().'][lastDLEpisode]" type="number" class="form-control" value="'.$item->getLastDLEpisode().'" style="width:60px"/>'.
                            '</div>'.
                        '</div>'.
                    '</td>'.
                    '<td>'.
                        '<a href="?page=edit&id='.$item->getIMDBID().'" class="btn btn-default">'.
                            '<i class="glyphicon glyphicon-edit"></i> '.
                        '</a> '.
                        '<a href="?page=delete&id='.$item->getIMDBID().'" class="btn btn-danger">'.
                            '<i class="glyphicon glyphicon-remove-sign"></i> '.
                        '</a>'.
                    '</td>'.
                '</tr>';
        
                if($activeID===$item->getIMDBID()) {
                    $html .=
                    '<tr>'.
                        '<td colspan="7">'.
                            '<iframe style="width:100%;height:500px;border:0;margin:0;padding:0;" src="'.$item->getRarbgLink().'" seamless>'.
                            '</iframe>'.
                        '</td>'.
                    '</tr>';
                }
            }
            $html .=
        '</tbody>'.
    '</table>'.
    '<p>'.
        '<button name="update" type="submit" class="btn btn-primary" value="yes">'.
            '<i class="glyphicon glyphicon-edit"></i> '.
            'Update'.
        '</button>'.
    '</p>'.
'</form>';

echo $html;
