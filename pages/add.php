<?php 

$manager = Manager::getInstance();
$series = $manager->getSeries();


if(isset($_REQUEST['save']) && $_REQUEST['save']=='yes') {
    $name = cleanRequestVar('name');
    $tvcom = cleanRequestVar('tvcom');
    $rarbg = cleanRequestVar('rarbg');
    $tvdb = cleanRequestVar('tvdb');
    $series->add(array(
        'name' => $name, 
        'tvcom' => $tvcom, 
        'rarbg' => $rarbg,
        'tvdb' => $tvdb
    ));
    $series->save();

    header('Location:./');
    exit;
}

?>
<script src="js/add.js"></script>
<script>
$('document').ready(function() {
	$('#f-name').focus();
});
</script>
<h3>Add a series</h3>
<form method="post">
    <div class="form-group">
        <label for="f-name">Name</label>
        <input type="text" name="name" class="form-control" id="f-name" placeholder="Game of Thrones" onkeyup="UpdateSearchLinks()"/>
        <p class="help-block" id="searchlinks"></p>
    </div>
    <div class="form-group">
        <label for="f-tvcom">TV.com ID</label>
        <input type="text" name="tvcom" class="form-control" id="f-tvcom" placeholder="game-of-thrones"/>
        <p class="help-block">
            http://www.tv.com/shows/<span style="color:#cc0000">game-of-thrones</span>/episodes/
        </p>
    </div>
    <div class="form-group">
        <label for="f-rarbg">RARBG ID</label>
        <input type="text" name="rarbg" class="form-control" id="f-rarbg" placeholder="tt12345678"/>
        <p class="help-block">
            https://rarbg.to/tv/<span style="color:#cc0000">tt2234222</span>/
        </p>
    </div>
    <div class="form-group">
        <label for="f-tvdb">TVDB ID</label>
        <input type="text" name="tvdb" class="form-control" id="f-tvdb" placeholder="123456"/>
        <p class="help-block">
            http://thetvdb.com/index.php?tab=series&amp;id=<span style="color:#cc0000">257655</span>&amp;lid=7
        </p>
    </div>
    <input type="hidden" name="add" value="yes"/>
    <input type="hidden" name="save" value="yes"/>
    <button type="submit" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i>
        Add series
    </button>
    <a href="./" class="btn btn-default">
        Cancel
    </a>
</form>
