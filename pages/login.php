<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager\Pages;

use Mistralys\SeriesManager\FormHandler;
use Mistralys\SeriesManager\Manager;
use function AppLocalize\pt;
use function AppLocalize\t;

$manager = Manager::getInstance();

if($manager->isLoggedIn())
{
    if(isset($_REQUEST['logout']) && $_REQUEST['logout'] === 'yes')
    {
        unset($_SESSION['auth']);

        header('Location:?page=login');
        exit;
    }

    ?>
        <p>
            <?php pt('You are currently logged in.') ?>
        </p>
        <p>
            <a class="btn btn-primary" href="?page=login&amp;logout=yes">
                <?php pt('Log out') ?>
            </a>
        </p>
    <?php
    return;
}

$formHandler = new FormHandler('seriesmanager-login');
$form = $formHandler->getForm();

$el = $form->addPassword('password');
$el->setLabel(t('Password'));
$el->addFilter('trim');
$el->addClass('form-control');
$el->addRuleCallback(t('Invalid password.'), array($manager, 'isPasswordValid'));

$btn = $form->addButton('save');
$btn->setAttribute('type', 'submit');
$btn->setContent(t('Sign in'));
$btn->addClass('btn btn-primary');

if($formHandler->isValid())
{
    $values = $formHandler->getValues();

    $_SESSION['auth'] = $manager->encodePassword($values['password']);

    header('Location:?page=list');
    exit;
}

?>
<h3><?php pt('Login') ?></h3>
<p>
	<?php pt('Please log in with the configured password.') ?>
</p>
<?php $formHandler->display() ?>
