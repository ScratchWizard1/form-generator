<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Button extends BasicComponent
{
    private string $buttonText;

    /**
     * Constructor for Button component.
     *
     * @param string $name
     * @param string $buttonText
     */
    public function __construct(
        string $name,
        string $buttonText = ""
    ) {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
        $this->buttonText = $buttonText;
    }

    /**
     * Sets the button text.
     *
     * @param string $buttonText
     */
    public function setText(string $buttonText): void
    {
        $this->buttonText = $buttonText;
    }

    /**
     * Generate the HTML for the button input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'id' => $this->id,
            'onclick' => $this->onclick,
            'name' => $this->name,
            'value' => $this->value,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'autofocus' => $this->autofocus,
            'hidden' => $this->hidden,
            'title' => $this->title
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<button'
                . Globals::toStr($htmlAttributes) . '>'
                . Globals::toStr($this->buttonText)
                . '</button>';
        } else {
            $input = '<button'
                . Globals::toStr($htmlAttributes) . '>'
                . Globals::toStr($this->buttonText)
                . '</button>';
        }
        return $input;
    }
}
