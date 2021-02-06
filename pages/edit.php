<?php 

$manager = Manager::getInstance();
$series = $manager->getSeries();
$selected = $manager->getSelected();

if(isset($_REQUEST['save']) && $_REQUEST['save']=='yes') {
    $selected->setName(cleanRequestVar('name'));
    $selected->setTvcomID(cleanRequestVar('tvcom'));
    $selected->setRarbgID(cleanRequestVar('rarbg'));
    $selected->setTvdbID(cleanRequestVar('tvdb'));
    
    $series->save();

    header('Location:./');
    exit;
}

$data = array(
    'rarbg' => $selected->getRarbgID(),
    'tvcom' => $selected->getTvcomID(),
    'tvdb' => $selected->getTvdbID(),
    'name' => $selected->getName()
);

?>
<h3>Edit a series</h3>
<form method="post">
    <div class="form-group">
        <label for="f-name">Name</label>
        <input type="text" name="name" class="form-control" id="f-name" value="<?php echo $data['name'] ?>"/>
    </div>
    <div class="form-group">
        <label for="f-tvcom">TV.com ID</label>
        <input type="text" name="tvcom" class="form-control" id="f-tvcom" value="<?php echo $data['tvcom'] ?>"/>
        <p class="help-block">
            http://www.tv.com/shows/<span style="color:#cc0000">game-of-thrones</span>/episodes/
        </p>
    </div>
    <div class="form-group">
        <label for="f-rarbg">RARBG ID</label>
        <input type="text" name="rarbg" class="form-control" id="f-rarbg" value="<?php echo $data['rarbg'] ?>"/>
        <p class="help-block">
            https://rarbg.to/tv/<span style="color:#cc0000">tt2234222</span>/
        </p>
    </div>
    <div class="form-group">
        <label for="f-tvdb">TVDB ID</label>
        <input type="text" name="tvdb" class="form-control" id="f-tvdb" value="<?php echo $data['tvdb'] ?>"/>
        <p class="help-block">
            http://thetvdb.com/index.php?tab=series&amp;id=<span style="color:#cc0000">257655</span>&amp;lid=7
        </p>
    </div>
    <input type="hidden" name="edit" value="yes"/>
    <input type="hidden" name="id" value="<?php echo $selected->getRarbgID() ?>"/>
    <input type="hidden" name="save" value="yes"/>
    <button type="submit" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i>
        Save now
    </button>
    <a href="./" class="btn btn-default">
        Cancel
    </a>
</form>
