<?php

class Bootstrap3Renderer extends HTML_QuickForm2_Renderer
{
   /**
    * @var array
    */
    protected $hiddens = array();
    
   /**
    * @var string[]
    */
    protected $contents = array();
    
   /**
    * @var string
    */
    protected $formStart = '';
    
   /**
    * @var string
    */
    protected $formEnd = ''; 
    
   /**
    * @var HTML_QuickForm2_Element_Button
    */
    protected $buttons = array();
    
    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        $this->hiddens[] = '<input '.$element->getAttributes(true).'>';
    }

    public function renderElement(HTML_QuickForm2_Node $element)
    {
        ob_start();
        
        $errorClass = '';
        
        if($element->hasErrors())
        {
            $errorClass = 'has-error';
        }
        
        if($element instanceof HTML_QuickForm2_Element_InputCheckbox)
        {
            ?>
            	<div class="checkbox <?php echo $errorClass ?>">
                    <label>
	                    <?php echo (string)$element ?> <?php echo $element->getLabel() ?>
                    </label>
                    <p class="help-block"><?php echo $element->getComment() ?></p>
                </div>
            <?php 
        }
        if($element instanceof HTML_QuickForm2_Element_InputRadio)
        {
            ?>
                <div class="radio <?php echo $errorClass ?>">
                    <label>
	                    <?php echo (string)$element ?>
                    	<?php echo $element->getLabel() ?>
                    </label>
                    <p class="help-block"><?php echo $element->getComment() ?></p>
                </div>
            <?php 
        }
        if($element instanceof HTML_QuickForm2_Element_Button)
        {
            $this->buttons[] = $element;
        }
        else
        {
            ?>
            	<div class="form-group <?php echo $errorClass ?>">
            		<label for="<?php echo $element->getId() ?>"><?php echo $element->getLabel() ?></label>
            		<?php echo (string)$element ?>
            		<p class="help-block"><?php echo $element->getComment() ?></p>
            	</div>
        	<?php
        }
    	
    	$this->contents[] = ob_get_clean();
    }

    public function startGroup(HTML_QuickForm2_Node $group)
    {
        
    }

    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        
    }

    public function reset()
    {
        
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->formStart = '<form '.$form->getAttributes(true).'>';
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->formEnd = '</form>';
    }

    public function startContainer(HTML_QuickForm2_Node $container)
    {
        
    }

    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        
    }
    
    public function __toString()
    {
        ob_start();
        
        ?>
        	<?php echo $this->formStart ?>
        	
        	<div class="hiddens">
        		<?php echo implode(PHP_EOL, $this->hiddens) ?>
        	</div>
        	<div class="form-elements">
        		<?php echo implode(PHP_EOL, $this->contents) ?>
        	</div>
        	<div class="form-buttons">
        		<?php echo implode(PHP_EOL, $this->buttons) ?>
        	</div>
        	
        	<?php echo $this->formEnd ?>
        <?php 
        
        return ob_get_clean();
    }
}
