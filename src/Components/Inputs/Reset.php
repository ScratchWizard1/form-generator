<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Reset extends BasicComponent
{
    /**
     * Constructor for Reset component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
        $this->value = $name;
    }

    /**
     * Generate the HTML for the reset input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'name' => $this->name,
            'id' => $this->id,
            'onclick' => $this->onclick,
            'value' => $this->value,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'hidden' => $this->hidden,
            'autofocus' => $this->autofocus,
            'title' => $this->title
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<input type="reset"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="reset"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
