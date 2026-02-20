<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Label extends BasicComponent
{
    private string $for;

    /**
     * Constructor for Label component.
     *
     * @param string $label
     * @param string $for
     */
    public function __construct(string $label, string $for)
    {
        $this->label = $label;
        $this->for = $for;
        $this->name = $label;
        $this->id = $label;
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
     * Sets the for attribute for the label.
     *
     * @param string $id
     * @return static
     */
    public function for(string $id): static
    {
        $this->for = $id;
        return $this;
    }

    /**
     * Generate the HTML for the label.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'for' => $this->for,
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
        return "<label"
            . Globals::toStr($htmlAttributes) . ">"
            . Globals::toStr($this->label)
            . "</label>";
    }
}
