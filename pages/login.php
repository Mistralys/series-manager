<?php

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
        		You are currently logged in.
        	</p>
        	<p>
        		<a class="btn btn-primary" href="?page=login&amp;logout=yes">
        			Log out
        		</a>
        	</p>
        <?php
        return;
    }

    $form = new HTML_QuickForm2('seriesmanager-login');
    
    $el = $form->addPassword('password');
    $el->setLabel('Password');
    $el->addFilter('trim');
    $el->addClass('form-control');
    $el->addRuleCallback('Invalid password.', array($manager, 'isPasswordValid'));
    
    $btn = $form->addButton('save');
    $btn->setAttribute('type', 'submit');
    $btn->setContent('Sign in');
    $btn->addClass('btn btn-primary');

    HTML_QuickForm2_Renderer::register('Bootstrap3', 'Bootstrap3Renderer');
    
    $renderer = HTML_QuickForm2_Renderer::factory('Bootstrap3');
    
    if($form->isSubmitted() && $form->validate())
    {
        $values = $form->getValues();
        
        $_SESSION['auth'] = $manager->encodePassword($values['password']);
        
        header('Location:?page=list');
        exit;
    }
    
?>
<h3>Login</h3>
<p>
	Please log in with the configured password.
</p>
<?php echo $form->render($renderer) ?>
