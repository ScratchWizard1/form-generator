<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Legend extends BasicComponent
{
    private ?string $legend = null;

    /**
     * Constructor for Legend component.
     *
     * @param string $legend
     */
    public function __construct(string $legend)
    {
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
     * Generate the HTML for the legend.
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
        return "<legend"
            . Globals::toStr($htmlAttributes) . ">"
            . Globals::toStr($this->legend)
            . "</legend>";
    }
}
