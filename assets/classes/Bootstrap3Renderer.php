<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use AppUtils\OutputBuffering;
use HTML_QuickForm2_Container_Group;
use HTML_QuickForm2_Element_Button;
use HTML_QuickForm2_Element_InputCheckbox;
use HTML_QuickForm2_Element_InputRadio;
use HTML_QuickForm2_Node;
use HTML_QuickForm2_Renderer;

class Bootstrap3Renderer extends HTML_QuickForm2_Renderer
{
    /**
     * @var string[]
     */
    protected array $hiddens = array();

    /**
     * @var string[]
     */
    protected array $contents = array();

    protected string $formStart = '';

    protected string $formEnd = '';

    /**
     * @var HTML_QuickForm2_Element_Button[]
     */
    protected array $buttons = array();

    public function renderHidden(HTML_QuickForm2_Node $element) : void
    {
        $this->hiddens[] = '<input ' . $element->getAttributes(true) . '>';
    }

    public function renderElement(HTML_QuickForm2_Node $element) : void
    {
        OutputBuffering::start();

        $errorClass = '';

        if ($element->hasErrors())
        {
            $errorClass = 'has-error';
        }

        if ($element instanceof HTML_QuickForm2_Element_InputCheckbox)
        {
            ?>
            <div class="checkbox <?php echo $errorClass ?>">
                <label>
                    <?php echo $element ?><?php echo $element->getLabel() ?>
                </label>
                <p class="help-block"><?php echo $element->getComment() ?></p>
            </div>
            <?php
        }
        if ($element instanceof HTML_QuickForm2_Element_InputRadio)
        {
            ?>
            <div class="radio <?php echo $errorClass ?>">
                <label>
                    <?php echo $element ?>
                    <?php echo $element->getLabel() ?>
                </label>
                <p class="help-block"><?php echo $element->getComment() ?></p>
            </div>
            <?php
        }
        if ($element instanceof HTML_QuickForm2_Element_Button)
        {
            $this->buttons[] = $element;
        }
        else
        {
            $element->addClass('form-control');

            ?>
            <div class="form-group <?php echo $errorClass ?>">
                <label for="<?php echo $element->getId() ?>"><?php echo $element->getLabel() ?></label>
                <?php echo $element ?>
                <p class="help-block"><?php echo $element->getComment() ?></p>
            </div>
            <?php
        }

        $this->contents[] = OutputBuffering::get();
    }

    public function startGroup(HTML_QuickForm2_Container_Group $group) : void
    {

    }

    public function finishGroup(HTML_QuickForm2_Container_Group $group) : void
    {

    }

    public function reset()
    {

    }

    public function startForm(HTML_QuickForm2_Node $form) : void
    {
        $this->formStart = '<form ' . $form->getAttributes(true) . '>';
    }

    public function finishForm(HTML_QuickForm2_Node $form) : void
    {
        $this->formEnd = '</form>';
    }

    public function startContainer(HTML_QuickForm2_Node $container) : void
    {

    }

    public function finishContainer(HTML_QuickForm2_Node $container) : void
    {

    }

    public function __toString()
    {
        OutputBuffering::start();

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

        return OutputBuffering::get();
    }
}
