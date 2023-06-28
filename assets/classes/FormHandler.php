<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

use HTML_QuickForm2;
use HTML_QuickForm2_Renderer;
use HTML_QuickForm2_Renderer_Proxy;

class FormHandler
{
    public const RENDERER_ID = 'Bootstrap3';

    private HTML_QuickForm2 $form;
    private HTML_QuickForm2_Renderer_Proxy $renderer;

    public function __construct(string $id)
    {
        $this->form = new HTML_QuickForm2($id);

        HTML_QuickForm2_Renderer::register(self::RENDERER_ID, Bootstrap3Renderer::class);

        $this->renderer = HTML_QuickForm2_Renderer::factory(self::RENDERER_ID);
    }

    public function getForm() : HTML_QuickForm2
    {
        return $this->form;
    }

    public function isValid() : bool
    {
        return $this->form->isSubmitted() && $this->form->validate();
    }

    public function getValues() : array
    {
        return $this->form->getValues();
    }

    public function render() : string
    {
        return (string)$this->form->render($this->renderer);
    }

    public function display() : void
    {
        echo $this->render();
    }

    public function addHiddenVar(string $name, string $value) : self
    {
        $this->form->addHidden($name, array('value' => $value));
        return $this;
    }
}
