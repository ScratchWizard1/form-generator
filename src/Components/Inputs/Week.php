<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Week extends BasicComponent
{
    /**
     * Constructor for Week component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
    }

    /**
     * Generate the HTML for the week input.
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
            'min' => $this->min,
            'max' => $this->max,
            'autofocus' => $this->autofocus,
            'hidden' => $this->hidden,
            'title' => $this->title,
            'step' => $this->step
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<input type="week"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="week"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
