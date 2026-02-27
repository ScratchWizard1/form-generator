<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class Option extends BasicComponent
{
    private string $labelOption;
    private Select $parentSelect;

    /**
     * Constructor for Option component.
     * @param string $label
     * @param Select $parentSelect
     */
    public function __construct(string $label, Select $parentSelect)
    {
        $this->value = $label;
        $this->labelOption = $label;
        $this->parentSelect = $parentSelect;
    }

    /**
     * Add an option to the parent select.
     *
     * @param string $label
     * @return Select|Option
     */
    public function option(string $label): Select|Option
    {
        return $this->parentSelect->option($label);
    }

    /**
     * Generate the HTML for the option element.
     *
     * @return string
     */
    public function getOption(): string
    {
        $attributes = [
            'value' => $this->value,
            'disabled' => $this->disabled,
            'selected' => $this->selected,
            'class' => $this->class,
            'style' => $this->style,
            'id' => $this->id,
            'onclick' => $this->onclick,
            'hidden' => $this->hidden,
            'title' => $this->title
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);

        if ($this->selected) {
            $this->parentSelect->selected = true;
        }

        return '<option'
            . Globals::toStr($htmlAttributes) . '>'
            . Globals::toStr($this->labelOption)
            . '</option>';
    }

    /**
     * Get the HTML for the option element.
     *
     * @return string
     */
    public function getHTML(): string
    {
        return $this->getOption();
    }

    /**
     * Echo the HTML for the option element.
     *
     * @return void
     */
    public function get(): void
    {
        echo $this->getOption();
    }
}
