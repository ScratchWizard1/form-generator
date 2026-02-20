<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Range extends BasicComponent
{
    /**
     * Constructor for Range component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
    }

    /**
     * Generate the HTML for the range input.
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
            'autofocus' => $this->autofocus,
            'list' => $this->list,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'step' => $this->step,
            'min' => $this->min,
            'max' => $this->max,
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
                . '<input tape="range"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="range"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
