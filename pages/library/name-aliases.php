<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages\Library;

const SETTING_NAME = 'detected_name';
const SETTING_ALIAS = 'name_alias';
const REQUEST_VAR_DELETE_ALIAS = 'delete-alias';

use AppUtils\Request;
use HTML_QuickForm2_Rule_Required;
use Mistralys\SeriesManager\FormHandler;
use Mistralys\SeriesManager\Manager\Library;
use function AppLocalize\pt;
use function AppLocalize\pts;
use function AppLocalize\t;
use function AppUtils\sb;

$library = Library::createFromConfig();

$request = new Request();
$alias = (string)$request
    ->registerParam(REQUEST_VAR_DELETE_ALIAS)
    ->setEnum(array_keys($library->getNameAliases()))
    ->get();

if(!empty($alias))
{
    $library->deleteNameAlias($alias);
    header('Location:'.$library->getURLNameAliases());
}

?>
<p>
    <?php
    pts('To handle cases where the automatic series name detection fails, you can add name aliases.');
    pts('Simply specify the auto-detected series name, and the name this should be replaced with.');
    pts('Afterwards, refresh the library to adjust the detection.');
    ?>
</p>
<?php

$aliases = $library->getNameAliases();

if(!empty($aliases))
{
    ?>
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th><?php pt('Auto-detected name'); ?></th>
            <th><?php pt('Replace with') ?></th>
            <th style="width: 1px"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($aliases as $name => $alias)
        {
            $url = $library->getURLNameAliases(array(
                REQUEST_VAR_DELETE_ALIAS => $name
            ));

            ?>
            <tr>
                <td><?php echo $name ?></td>
                <td><?php echo $alias ?></td>
                <td style="white-space: nowrap">
                    <a href="<?php echo $url ?>" class="text-danger">
                        <i class="glyphicon glyphicon-remove-sign"></i>
                        <?php pt('Delete') ?>
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
}
else
{
    ?>
    <div class="alert alert-info">
        <?php pt('No name aliases defined.') ?>
    </div>
    <?php
}
?>
<hr>
<h4><?php pt('Add an alias') ?></h4>
<?php
$formHandler = new FormHandler('name-aliases');
$form = $formHandler->getForm();

$formHandler->addHiddenVar('page', 'library');
$formHandler->addHiddenVar('tab', Library::TAB_NAME_ALIASES);

$name = $form->addSelect(SETTING_NAME);
$name->setLabel(t('Auto-detected name'));
$seriesNames = $library->getSeriesNames();
foreach($seriesNames as $seriesName) {
    $name->addOption($seriesName, $seriesName);
}

$alias = $form->addText(SETTING_ALIAS);
$alias->addFilterTrim();
$alias->addFilter('mb_strtolower');
$alias->setLabel(t('Replace with'));
$alias->setComment(sb()
    ->t('Will automatically replace the auto-detected name.')
    ->t('It is not case sensitive.')
    ->noteBold()
    ->t('This is only used to correctly detect existing episodes on disk.')
    ->t('The library uses it to match it to the actual series\' name.')
);

$alias->addRule(new HTML_QuickForm2_Rule_Required($alias, 'Reuiqre'));
$alias->addRuleCallback(
    t('Invalid name.'),
    static function($value) : bool
    {
        if(empty($value) || !is_string($value)) {
            return true;
        }

        return preg_match('/\A[a-z0-9 \']+\Z/', $value) === 1;
    }
);

$btn = $form->addButton('save');
$btn->setAttribute('type', 'submit');
$btn->setContent(
    '<i class="glyphicon glyphicon-plus-sign"></i> '.
    t('Add now')
);
$btn->addClass('btn btn-primary');

if($formHandler->isValid())
{
    $values = $formHandler->getValues();

    $library->setNameAlias($values[SETTING_NAME], $values[SETTING_ALIAS]);

    header('Location:'.$library->getURLNameAliases());
    exit;
}

$formHandler->display();
