<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Submit extends BasicComponent
{
    /**
     * Constructor for Submit component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->id = $this->normalizeId($name);
        $this->value = $name;
    }


    /**
     * Generate the HTML for the submit input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'name' => $this->name,
            'id' => $this->id,
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
                . '<input type="submit"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="submit"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
