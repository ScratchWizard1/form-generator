<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Image extends BasicComponent
{
    /**
     * Constructor for Image component.
     *
     * @param string $name
     * @param string $src
     * @param string $alt
     */
    public function __construct(string $name, string $src, string $alt)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
        $this->src = $src;
        $this->alt = $alt;
    }

    /**
     * Generate the HTML for the image input.
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
            'src' => $this->src,
            'alt' => $this->alt,
            'disabled' => $this->disabled,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'width' => $this->width,
            'height' => $this->height,
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
                . '<input type="image"'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input type="image"'
                . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }
}
