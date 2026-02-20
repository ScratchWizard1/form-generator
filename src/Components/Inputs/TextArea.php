<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class TextArea extends BasicComponent
{
    /**
     * Constructor for TextArea component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
    }

    /**
     * Generate the HTML for the textarea input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'name' => $this->name,
            'id' => $this->id,
            'onclick' => $this->onclick,
            'placeholder' => $this->placeholder,
            'maxlength' => $this->maxlength,
            'minlength' => $this->minlength,
            'required' => $this->required,
            'readonly' => $this->readonly,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'autofocus' => $this->autofocus,
            'spellcheck' => $this->spellcheck,
            'wrap' => $this->wrap,
            'rows' => $this->rows,
            'cols' => $this->cols,
            'dir' => $this->dir,
            'hidden' => $this->hidden,
            'title' => $this->title
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for=\""
                . Globals::toStr($this->id) . "\">"
                . Globals::toStr($this->label) . "</label>";
            if ($this->value !== null) {
                $input .= '<textarea'
                    . Globals::toStr($htmlAttributes) . '>'
                    . Globals::toStr($this->value)
                    . '</textarea>';
            } else {
                $input .= '<textarea'
                    . Globals::toStr($htmlAttributes)
                    . '></textarea>';
            }
        } else {
            if ($this->value !== null) {
                $input = '<textarea'
                    . Globals::toStr($htmlAttributes) . '>'
                    . Globals::toStr($this->value)
                    . '</textarea>';
            } else {
                $input = '<textarea'
                    . Globals::toStr($htmlAttributes)
                    . '></textarea>';
            }
        }
        return $input;
    }
}
