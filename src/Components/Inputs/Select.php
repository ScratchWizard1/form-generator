<?php

declare(strict_types=1);

namespace FormGenerator\Components\Inputs;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Components\Inputs\Option;
use FormGenerator\Config\Globals;
use FormGenerator\ErrorHandling\FormErrorHandler;

class Select extends BasicComponent
{
    /**
     * @var array<Option> $options
     */
    private array $options = [];
    private string $select;


    /**
     * Constructor for Select component.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = $this->normalizeId($name);
    }

    /**
     * Add an option or multiple options to the select.
     *
     * @param string|array<mixed> $label
     * @param array<mixed> $value
     * @param string $selected
     * @return Option|static
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     */
    public function option(string|array $label, array $value = [], string $selected = ""): Option|static
    {
        if (is_string($label)) {
            $option = new Option($label, $this);
            $this->options[] = $option;
            return $option;
        }
        if ($value != []) {
            if (count($label) != count($value)) {
                throw FormErrorHandler::exception(
                    "Label and value array must have the same length",
                    400,
                    "fE008"
                );
            }
        }
        for ($i = 0; $i < count($label); $i++) {
            if ($value == []) {
                if ($label[$i] == $selected) {
                    $option = new Option(Globals::toStr($label[$i]), $this);
                    $option->selected();
                    $this->options[] = $option;
                } else {
                    $option = new Option(Globals::toStr($label[$i]), $this);
                    $this->options[] = $option;
                }
            } else {
                if ($label[$i] == $selected) {
                    $option = new Option(Globals::toStr($label[$i]), $this);
                    $option->selected()->value(Globals::toStr($value[$i]));
                    $this->options[] = $option;
                } else {
                    $option = new Option(Globals::toStr($label[$i]), $this);
                    $option->value(Globals::toStr($value[$i]));
                    $this->options[] = $option;
                }
            }
        }
        return $this;
    }

    /**
     * Generate the HTML for the select input.
     *
     * @return string
     */
    protected function getInput(): string
    {
        $attributes = [
            'name' => $this->name,
            'id' => $this->id,
            'onclick' => $this->onclick,
            'class' => $this->class,
            'style' => $this->style,
            'form' => $this->form,
            'size' => $this->size,
            'multiple' => $this->multiple,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'autofocus' => $this->autofocus,
            'hidden' => $this->hidden,
            'title' => $this->title
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);

        if ($this->label !== null) {
            $this->select = "<label for='"
                . Globals::toStr($this->id) . "'>"
                . Globals::toStr($this->label)
                . "</label>"
                . '<select'
                . Globals::toStr($htmlAttributes) . '>';
        } else {
            $this->select = '<select'
                . Globals::toStr($htmlAttributes) . '>';
        }
        foreach ($this->options as $option) {
            $this->select .= Globals::toStr($option->getOption());
        }
        if (!$this->selected) {
            $this->select .= '<option value="" disabled selected hidden >--Select--</option>';
        }
        $this->select .= '</select>';

        return $this->select;
    }
}
