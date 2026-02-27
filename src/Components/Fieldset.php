<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Fieldset extends BasicComponent
{
    private ?string $legend = null;

    /**
     * Constructor for Fieldset component.
     *
     * @param string $legend
     */
    public function __construct(string $legend = "")
    {
        if (trim($legend) == "") {
            $legend = null;
        }
        $this->legend = $legend;
    }

    /**
     * Set the name of the component.
     *
     * @param string $name
     * @return static
     */
    public function name(string $name): static
    {
        if (!empty(trim($name))) {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * Sets the for attribute for the fieldset.
     *
     * @param string $id
     * @return static
     */
    public function for(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Generate the HTML for the fieldset input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'id' => $this->id,
            'onclick' => $this->onclick,
            'name' => $this->name,
            'class' => $this->class,
            'style' => $this->style,
            'title' => $this->title,
            'hidden' => $this->hidden
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        return "<fieldset"
            . Globals::toStr($htmlAttributes) . ">"
            . ($this->legend ? "<legend>"
                . Globals::toStr($this->legend)
                . "</legend>" : "");
    }
}
