<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Search extends BasicComponent
{
    /**
     * Constructor for Search component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
    }

    /**
     * Generate the HTML for the search input.
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
            'placeholder' => $this->placeholder,
            'autofocus' => $this->autofocus,
            'maxlength' => $this->maxlength,
            'minlength' => $this->minlength,
            'size' => $this->size,
            'required' => $this->required,
            'readonly' => $this->readonly,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'dir' => $this->dir,
            'title' => $this->title,
            'autocomplete' => $this->autocomplete,
            'list' => $this->list,
            'hidden' => $this->hidden,
            'spellcheck' => $this->spellcheck
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<input type="search"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="search"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
