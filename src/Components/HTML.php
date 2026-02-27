<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Form;
use FormGenerator\Components\BasicComponent;
use FormGenerator\Config\Globals;

class HTML extends BasicComponent
{
    private string $html = "";
    private Form $parent;
    private string $formID;

    /**
     * Constructor for HTML component.
     *
     * @param string $html
     * @param string $formID
     * @param Form $parent
     */
    public function __construct(
        string $html,
        string $formID,
        Form $parent
    ) {
        $this->html = $html;
        $this->parent = $parent;
        $this->formID = $formID;
    }
    /**
     * Co for HTML component.
     *
     * @param string $html
     * @return HTML
     */
    public function html(string $html): HTML
    {
        return $this->parent->html($html);
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
     * Generate the HTML for the HTML component.
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
        if (isset($attributes['name'])) {
            if (
                preg_match('/^<(input|select|textarea)\b/i', strtolower($this->html))
            ) {
                $attributes['name'] = Globals::toStr($this->formID)
                    . "["
                    . Globals::toStr($attributes['name'])
                    . "]";
            }
        }
        $htmlAttributes = $this->htmlAttributes($attributes);
        preg_match('/<[^>]*>/', $this->html, $matches);
        if (isset($matches[0])) {
            $this->html = str_replace(Globals::toStr($matches[0]), '', Globals::toStr($this->html));
            $matches[0] = str_replace('>', '', $matches[0]);
            $matches[0] = $matches[0] . Globals::toStr($htmlAttributes) . '>';
            $this->html = Globals::toStr($matches[0]) . Globals::toStr($this->html);
        }
        return $this->html;
    }
}
