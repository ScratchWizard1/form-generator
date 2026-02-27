<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Password extends BasicComponent
{
    /**
     * Constructor for Password component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
        $this->minlength = 8;
        $this->pattern = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W]{8,}$";
        $this->title = 'Password must contain at least one uppercase letter, 
        one lowercase letter, one number and be at least 8 characters long.';
    }

    /**
     * Generate the HTML for the password input.
     *
    toStr@return string
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
            'hidden' => $this->hidden,
            'pattern' => $this->pattern
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<input type="password"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="password"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
