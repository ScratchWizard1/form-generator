<?php

declare(strict_types=1);

namespace FormGenerator\Components;

use FormGenerator\Config\Globals;

class BasicComponent
{
    /**
     * @var array<string, mixed>|null $attributes
     */
    protected ?array $attributes = null;
    protected ?string $id = null;
    protected ?string $name = null;
    protected ?string $class = null;
    protected ?string $style = null;
    protected ?string $value = null;
    protected ?string $placeholder = null;
    protected bool $required = false;
    protected ?bool $disabled = null;
    protected ?bool $readonly = null;
    protected null|string|int $maxlength = null;
    protected null|string|int $minlength = null;
    protected ?int $size = null;
    protected ?string $pattern = null;
    protected string $autocomplete = 'on';
    protected bool $multiple = false;
    protected ?string $list = null;
    protected bool $autofocus = false;
    protected ?string $form = null;
    protected null|int|string|float $min = null;
    protected null|int|string|float $max = null;
    protected ?string $accept = null;
    protected float|int|string $step = "any";
    protected ?string $spellcheck = null;
    protected string $wrap = 'soft';
    protected bool $hidden = false;
    protected ?int $rows = null;
    protected ?int $cols = null;
    protected ?string $dir = null;
    protected ?string $title = null;
    protected bool $checked = false;
    protected ?string $src = null;
    protected ?string $alt = null;
    protected ?int $width = null;
    protected ?int $height = null;
    protected ?bool $selected = null;
    protected ?string $type = null;
    protected ?string $label = null;
    protected ?string $onclick = null;


    /**
     * Set the ID of the component.
     *
     * @param string $id
     * @return static
     */
    public function id(string $id): static
    {
        $id = $this->normalizeId($id);
        if (!empty(trim($id))) {
            $this->id = $id;
        }
        return $this;
    }

    /**
     * Set the value of the component.
     *
     * @param string $value
     * @return static
     */
    public function value(string $value): static
    {
        if (!empty(trim($value))) {
            $this->value = $value;
        }
        return $this;
    }

    /**
     * Set the placeholder of the component.
     *
     * @param string $placeholder
     * @return static
     */
    public function placeholder(string $placeholder): static
    {
        if (!empty(trim($placeholder))) {
            $this->placeholder = $placeholder;
        }
        return $this;
    }

    /**
     * Set the autofocus attribute of the component.
     *
     * @return static
     */
    public function autofocus(): static
    {
        $this->autofocus = true;
        return $this;
    }

    /**
     * Set the maxlength attribute of the component.
     *
     * @param int|string $maxlength
     * @return static
     */
    public function maxlength(int|string $maxlength): static
    {
        if (is_string($maxlength)) {
            $maxlength = preg_replace('/\D/', '', $maxlength);
            $maxlength = (int) $maxlength;
        }
        if (!empty($maxlength)) {
            $this->maxlength = $maxlength;
        }
        return $this;
    }

    /**
     * Set the minlength attribute of the component.
     *
     * @param int|string $minlength
     * @return static
     */
    public function minlength(int|string $minlength): static
    {
        if (is_string($minlength)) {
            $minlength = preg_replace('/\D/', '', $minlength);
            $minlength = (int) $minlength;
        }
        if (!empty($minlength)) {
            $this->minlength = $minlength;
        }
        return $this;
    }

    /**
     * Set the size attribute of the component.
     *
     * @param int $size
     * @return static
     */
    public function size(int $size): static
    {
        if (!empty($size)) {
            $this->size = $size;
        }
        return $this;
    }

    /**
     * Set the pattern attribute of the component.
     *
     * @param string $pattern
     * @return static
     */
    public function pattern(string $pattern): static
    {
        if (!empty(trim($pattern))) {
            $this->pattern = $pattern;
        }
        return $this;
    }

    /**
     * Set the autocomplete attribute of the component.
     *
     * @param bool|string $autocomplete
     * @return static
     */
    public function autocomplete(bool|string $autocomplete): static
    {
        if (is_bool($autocomplete)) {
            if ($autocomplete) {
                $this->autocomplete = 'on';
            } else {
                $this->autocomplete = 'off';
            }
        } else {
            if (!empty(trim($autocomplete))) {
                if (strtolower($autocomplete) == 'on') {
                    $this->autocomplete = 'on';
                } elseif (strtolower($autocomplete) == 'off') {
                    $this->autocomplete = 'off';
                } else {
                    $this->autocomplete = 'on';
                }
            }
        }

        return $this;
    }

    /**
     * Set the list attribute of the component.
     *
     * @param string $list
     * @return static
     */
    public function list(string $list): static
    {
        if (!empty(trim($list))) {
            $this->list = $list;
        }
        return $this;
    }

    /**
     * Set the multiple attribute of the component.
     *
     * @return static
     */
    public function multiple(): static
    {
        $this->multiple = true;
        return $this;
    }

    /**
     * Set the required attribute of the component.
     *
     * @return static
     */
    public function required(): static
    {
        $this->required = true;
        return $this;
    }

    /**
     * Set the readonly attribute of the component.
     *
     * @return static
     */
    public function readonly(): static
    {
        $this->readonly = true;
        return $this;
    }

    /**
     * Set the disabled attribute of the component.
     *
     * @return static
     */
    public function disabled(): static
    {
        $this->disabled = true;
        return $this;
    }

    /**
     * Set the class attribute of the component.
     *
     * @param string|array<string> $class
     * @return static
     */
    public function class(string|array $class): static
    {
        if (is_array($class)) {
            $class = implode(' ', $class);
        }
        if (!empty(trim($class))) {
            if ($this->class == null) {
                $this->class = $class;
            } else {
                $this->class .= ' ' . $class;
            }
        }
        return $this;
    }

    /**
     * Set custom attributes for the component.
     *
     * @param array<string, mixed> $attributes
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        if (!empty($attributes)) {
            if (!empty($this->attributes)) {
                $this->attributes = array_replace($this->attributes, $attributes);
            } else {
                $this->attributes = $attributes;
            }
        }
        return $this;
    }

    /**
     * Set the style attribute of the component.
     *
     * @param string|array<string> $style
     * @return static
     */
    public function style(string|array $style): static
    {
        if (is_array($style)) {
            $style = array_map(fn($styl) => rtrim($styl, ';') . ';', $style);
            $style = trim(implode(' ', $style));
        } else {
            $style = trim(rtrim($style, ';') . ';');
        }
        if (trim($style) != ";") {
            if ($this->style === null) {
                $this->style = $style;
            } else {
                $this->style .= ' ' . $style;
            }
        }
        return $this;
    }

    /**
     * Set the form attribute of the component.
     *
     * @param string $form
     * @return static
     */
    public function form(string $form): static
    {
        if (!empty(trim($form))) {
            $this->form = $form;
        }
        return $this;
    }

    /**
     * Set the min attribute of the component.
     *
     * @param int|string|float $min
     * @return static
     */
    public function min(int|string|float $min): static
    {
        if (is_string($min)) {
            $min = trim(str_replace(",", ".", $min));
        }
        if (!empty($min)) {
            $this->min = $min;
        }
        return $this;
    }

    /**
     * Set the max attribute of the component.
     *
     * @param int|string|float $max
     * @return static
     */
    public function max(int|string|float $max): static
    {
        if (is_string($max)) {
            $max = trim(str_replace(",", ".", $max));
        }
        if (!empty($max)) {
            $this->max = $max;
        }
        return $this;
    }

    /**
     * Set the accept attribute of the component.
     *
     * @param string|array<string> $accept
     * @return static
     */
    public function accept(string|array $accept): static
    {
        if (is_array($accept)) {
            $accept = implode(', ', $accept);
        }
        if (!empty(trim($accept))) {
            $this->accept = $accept;
        }
        return $this;
    }

    /**
     * Set the step attribute of the component.
     *
     * @param int|float|string $step
     * @return static
     */
    public function step(int|float|string $step): static
    {
        $step = Globals::toStr($step);
        if (!empty(trim($step))) {
            $this->step = $step;
        }
        return $this;
    }

    /**
     * Set the spellcheck attribute of the component.
     *
     * @param bool $spellcheck
     * @return static
     */
    public function spellcheck(bool $spellcheck): static
    {
        if ($spellcheck) {
            $this->spellcheck = "true";
        } else {
            $this->spellcheck = "false";
        }

        return $this;
    }

    /**
     * Set the wrap attribute of the component.
     *
     * @param string $wrap
     * @return static
     */
    public function wrap(string $wrap): static
    {
        if (!empty(trim($wrap))) {
            $this->wrap = $wrap;
        }
        return $this;
    }

    /**
     * Set the hidden attribute of the component.
     *
     * @return static
     */
    public function hidden(): static
    {
        $this->hidden = true;
        return $this;
    }

    /**
     * Set the rows attribute of the component.
     *
     * @param int $rows
     * @return static
     */
    public function rows(int $rows): static
    {
        if (!empty($rows)) {
            $this->rows = $rows;
        }
        return $this;
    }

    /**
     * Set the cols attribute of the component.
     *
     * @param int $cols
     * @return static
     */
    public function cols(int $cols): static
    {
        if (!empty($cols)) {
            $this->cols = $cols;
        }
        return $this;
    }

    /**
     * Set the dir attribute of the component.
     *
     * @param string $dir
     * @return static
     */
    public function dir(string $dir): static
    {
        if (!empty(trim($dir))) {
            switch (strtolower($dir)) {
                case 'ltr':
                    $this->dir = 'ltr';
                    break;
                case 'rtl':
                    $this->dir = 'rtl';
                    break;
                case 'auto':
                    $this->dir = 'auto';
                    break;
                default:
                    $this->dir = $dir;
            }
        }
        return $this;
    }

    /**
     * Set the title attribute of the component.
     *
     * @param string $title
     * @return static
     */
    public function title(string $title): static
    {
        if (!empty(trim($title))) {
            $this->title = Globals::esc($title);
        }
        return $this;
    }

    /**
     * Set the checked attribute of the component.
     *
     * @return static
     */
    public function checked(): static
    {
        $this->checked = true;
        return $this;
    }

    /**
     * Set the src attribute of the component.
     *
     * @param string $src
     * @return static
     */
    public function src(string $src): static
    {
        if (!empty(trim($src))) {
            $this->src = $src;
        }

        return $this;
    }

    /**
     * Set the alt attribute of the component.
     *
     * @param string $alt
     * @return static
     */
    public function alt(string $alt): static
    {
        if (!empty(trim($alt))) {
            $this->alt = $alt;
        }
        return $this;
    }

    /**
     * Set the width attribute of the component.
     *
     * @param int $width
     * @return static
     */
    public function width(int $width): static
    {
        if (!empty($width)) {
            $this->width = $width;
        }
        return $this;
    }

    /**
     * Set the height attribute of the component.
     *
     * @param int $height
     * @return static
     */
    public function height(int $height): static
    {
        if (!empty($height)) {
            $this->height = $height;
        }
        return $this;
    }

    /**
     * Set the selected attribute of the component.
     *
     * @return static
     */
    public function selected(): static
    {
        $this->selected = true;
        return $this;
    }

    /**
     * Set the type attribute of the component.
     *
     * @param string $type
     * @return static
     */
    public function type(string $type): static
    {
        if (!empty(trim($type))) {
            $this->type = $type;
        }
        return $this;
    }

    /**
     * Set the label of the component.
     *
     * @param string $label
     * @return static
     */
    public function label(string $label): static
    {
        if (!empty(trim($label))) {
            $this->label = $label;
            if ($this->id === null) {
                $this->id = $label;
            }
        }
        return $this;
    }

    /**
     * Set the onclick attribute of the component.
     *
     * @param string $onclick
     * @return static
     */
    public function onclick(string $onclick): static
    {
        if (!empty(trim($onclick))) {
            $this->onclick = $onclick;
        }
        return $this;
    }

    /**
     * Get the HTML string for the input element.
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
            'value' => $this->value,
            'hidden' => $this->hidden,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'readonly' => $this->readonly,
            'maxlength' => $this->maxlength,
            'minlength' => $this->minlength,
            'size' => $this->size,
            'pattern' => $this->pattern,
            'autocomplete' => $this->autocomplete,
            'multiple' => $this->multiple,
            'list' => $this->list,
            'autofocus' => $this->autofocus,
            'form' => $this->form,
            'min' => $this->min,
            'max' => $this->max,
            'accept' => $this->accept,
            'step' => $this->step,
            'spellcheck' => $this->spellcheck,
            'wrap' => $this->wrap,
            'rows' => $this->rows,
            'cols' => $this->cols,
            'dir' => $this->dir,
            'title' => $this->title,
            'checked' => $this->checked,
            'src' => $this->src,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'selected' => $this->selected,
            'type' => $this->type
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        if ($this->label !== null) {
            $input = "<label for='" . Globals::toStr($this->id) . "'>" .
                htmlspecialchars(
                    $this->label,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    Globals::getCharset()
                ) . "</label>" .
                '<input' . Globals::toStr($htmlAttributes) . '>';
        } else {
            $input = '<input' . Globals::toStr($htmlAttributes) . '>';
        }
        return $input;
    }

    /**
     * Process and escape the attributes for HTML output.
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function atributes(array $attributes): array
    {
        if ($this->attributes != null) {
            foreach ($this->attributes as $key => $value) {
                if ($key == 'style') {
                    if (is_array($value)) {
                        $value = Globals::toStr(implode(' ', $value));
                    }
                    $value = Globals::toStr($value);
                    $value = html_entity_decode(
                        $value,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    );
                    $attributes[$key] = Globals::toStr($attributes[$key] ?? '');
                    $attributes[$key] .= ' ' . Globals::toStr(htmlspecialchars(
                        $value,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    ));
                } elseif ($key == 'class') {
                    if (is_array($value)) {
                        $value = Globals::toStr(implode(' ', $value));
                    }
                    $value = Globals::toStr($value);
                    $value = html_entity_decode(
                        $value,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    );
                    $attributes[$key] = Globals::toStr($attributes[$key] ?? '');
                    $attributes[$key] .= ' ' . Globals::toStr(htmlspecialchars(
                        $value,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    ));
                } else {
                    if (is_string($value)) {
                        $value = html_entity_decode(
                            $value,
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            Globals::getCharset()
                        );
                        $attributes[$key] = htmlspecialchars(
                            $value,
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            Globals::getCharset()
                        );
                    } else {
                        $attributes[$key] = $value;
                    }
                }
            }
        }
        return $attributes;
    }

    /**
     * Generate HTML attributes string from the attributes array.
     *
     * @param array<mixed> $attributes
     * @return string
     */
    protected function htmlAttributes(array $attributes): string
    {
        $htmlAttributes = '';
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlAttributes .= ' ' . Globals::toStr($key);
            } elseif ($value !== null && $value !== '' && $value !== false) {
                $htmlAttributes .= ' ' . Globals::toStr($key) . '="' . Globals::toStr($value) . '"';
            }
        }
        return $htmlAttributes;
    }

    /**
     * Get the HTML string of the component.
     *
     * @return string
     */
    public function getHTML(): string
    {
        return $this->getInput();
    }

    /**
     * Output the HTML string of the component.
     *
     * @return void
     */
    public function get(): void
    {
        echo $this->getInput();
    }

    /**
     * Normalize the component name to create a valid HTML ID.
     *
     * @param string $name
     * @return string
     */
    protected function normalizeId(string $name): string
    {
        $id = preg_replace('/\]\[/', '_', $name) ?? $name;
        $id = str_replace(['[', ']'], '_', $id);
        $id = preg_replace('/_+/', '_', $id) ?? $id;
        $id = preg_replace('/_+$/', '', $id) ?? $id;

        if (str_ends_with($name, '[]')) {
            $id .= '_0';
        }

        $id = preg_replace('/[^A-Za-z0-9_-]/', '_', $id) ?? $id;

        if (preg_match('/^[0-9]/', $id)) {
            $id = 'id_' . $id;
        }

        $id = preg_replace('/_+/', '_', $id) ?? $id;

        return $id;
    }
}
