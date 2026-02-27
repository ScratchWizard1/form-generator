<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class CheckBox extends BasicComponent
{
    /**
     * Constructor for CheckBox component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        if (strpos($name, "]")) {
            $name = str_replace("]", "", $name);
        }
        if (strpos($name, "[")) {
            $name = str_replace("[", "", $name);
        }
        $this->id = $this->normalizeId($name);
    }

    /**
     * Generate the HTML for the checkbox input.
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
            'required' => $this->required,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'checked' => $this->checked,
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
                . '<input type="checkbox"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="checkbox"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
