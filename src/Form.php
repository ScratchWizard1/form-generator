<?php

declare(strict_types=1);

namespace FormGenerator;

use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\Config\Config;
use FormGenerator\DataProvider\FormDataProvider;
use FormGenerator\DataProvider\JsonDataProvider;
use FormGenerator\Components\HTML;
use FormGenerator\Components\Label;
use FormGenerator\Components\Fieldset;
use FormGenerator\Components\EndFieldset;
use FormGenerator\Components\Legend;
use FormGenerator\Components\Inputs\Text;
use FormGenerator\Components\Inputs\Password;
use FormGenerator\Components\Inputs\Search;
use FormGenerator\Components\Inputs\Email;
use FormGenerator\Components\Inputs\Tel;
use FormGenerator\Components\Inputs\Url;
use FormGenerator\Components\Inputs\Number;
use FormGenerator\Components\Inputs\Range;
use FormGenerator\Components\Inputs\Date;
use FormGenerator\Components\Inputs\Time;
use FormGenerator\Components\Inputs\DateTime;
use FormGenerator\Components\Inputs\Month;
use FormGenerator\Components\Inputs\Week;
use FormGenerator\Components\Inputs\CheckBox;
use FormGenerator\Components\Inputs\Radio;
use FormGenerator\Components\Inputs\File;
use FormGenerator\Components\Inputs\Hidden;
use FormGenerator\Components\Inputs\Color;
use FormGenerator\Components\Inputs\Button;
use FormGenerator\Components\Inputs\Image;
use FormGenerator\Components\Inputs\Submit;
use FormGenerator\Components\Inputs\Reset;
use FormGenerator\Components\Inputs\TextArea;
use FormGenerator\Components\Inputs\Select;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation\ValidationResult;
use FormGenerator\Config\Globals;

class Form
{
    /**
     * @var array<mixed>|null $attributes
     */

    private ?array $attributes = null;

    /**
     * @var array<int, (
     *     EndFieldset|
     *     Fieldset|
     *     HTML|
     *     Button|
     *     CheckBox|
     *     Color|
     *     Date|
     *     DateTime|
     *     Email|
     *     File|
     *     Hidden|
     *     Image|
     *     Month|
     *     Number|
     *     Password|
     *     Radio|
     *     Range|
     *     Reset|
     *     Search|
     *     Select|
     *     Submit|
     *     Tel|
     *     Text|
     *     TextArea|
     *     Time|
     *     Url|
     *     Week|
     *     Label|
     *     Legend
     * )> $form
     */
    private array $form;
    private ?string $action = null;
    private string $method = 'POST';
    private string $enctype = 'application/x-www-form-urlencoded';
    private string $target = '_self';
    private string $autocomplete = 'on';
    private bool $novalidate = false;
    private ?string $name = null;
    private ?string $id = null;
    private ?string $class = null;
    private ?string $style = null;
    private ?string $rel = null;
    private string $html;
    private string $formID;
    private bool $refreshProtection = false;
    private bool $captcha = false;
    private string $captchaAction = "GENERAL";
    private static string $sitekey = "";
    private static string $projectId = "";
    private static ?string $userProvidedCredentials = null;


    /**
     * Constructor for Form.
     *
     * @param string $action
     * @param string $formID
     */
    public function __construct(string $action, string $formID)
    {
        if (trim($formID) == "") {
            $formID = "form" . bin2hex(random_bytes(16));
        }
        $this->action = $action;
        $this->id = $this->normalizeId($formID);
        $this->formID = $formID;
    }

    /**
     * Static constructor for Form.
     *
     * @param string $action
     * @param string $formID
     * @return self
     */
    public static function new(string $action, string $formID): self
    {
        return new self($action, $formID);
    }

    /**
     * Set attribute ID for the form.
     *
     * @param string $id
     * @return static
     */
    public function id(string $id): static
    {
        if (!empty(trim($id))) {
            $this->id = $this->normalizeId($id);
        }
        return $this;
    }

    /**
     * Set attribute name for the form.
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
     * Set attribute class for the form.
     *
     * @param string|array<mixed> $class
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
     * Set attribute for the form.
     *
     * @param array<mixed> $attributes
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        if (isset($attributes['id']) && !empty($attributes['id'])) {
            $attributes['id'] = self::normalizeId(
                Globals::toStr($attributes['id'])
            );
        }
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
     * Set attribute style for the form.
     *
     * @param string|array<mixed> $style
     * @return static
     */
    public function style(string|array $style): static
    {
        if (is_array($style)) {
            $style = array_map(fn($styl) => rtrim(Globals::toStr($styl), ';') . ';', $style);
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
     * Set attribute rel for the form.
     *
     * @param string $rel
     * @return static
     */
    public function rel(string $rel): static
    {
        if (!empty(trim($rel))) {
            $this->rel = $rel;
        }
        return $this;
    }

    /**
     * Set attribute method for the form.
     *
     * @param string $method
     * @return static
     */
    public function method(string $method): static
    {
        if (!empty(trim($method))) {
            $method = strtoupper($method);
            if (in_array($method, ['POST', 'GET'])) {
                $this->method = $method;
            }
        }
        return $this;
    }

    /**
     * Set attribute action for the form.
     *
     * @param string $action
     * @return static
     */
    public function action(string $action): static
    {
        if (!empty(trim($action))) {
            $this->action = $action;
        }
        return $this;
    }

    /**
     * Set attribute enctype for the form.
     *
     * @param string $enctype
     * @return static
     */
    public function enctype(string $enctype): static
    {
        if (empty(trim($enctype))) {
            return $this;
        }
        if ($enctype == 'application' || $enctype == 'application/x-www-form-urlencoded') {
            $this->enctype = 'application/x-www-form-urlencoded';
        } elseif ($enctype == 'multipart' || $enctype == 'multipart/form-data') {
            $this->enctype = 'multipart/form-data';
        } elseif ($enctype == 'plain' || $enctype == 'text/plain') {
            $this->enctype = 'text/plain';
        } else {
            $this->enctype = $enctype;
        }
        return $this;
    }

    /**
     * Set attribute target for the form.
     *
     * @param string $target
     * @return static
     */
    public function target(string $target): static
    {
        if (!empty(trim($target))) {
            $this->target = $target;
        }
        return $this;
    }

    /**
     * Set attribute autocomplete for the form.
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
            if (empty(trim($autocomplete))) {
                return $this;
            }
            if (strtolower($autocomplete) == 'on') {
                $this->autocomplete = 'on';
            } elseif (strtolower($autocomplete) == 'off') {
                $this->autocomplete = 'off';
            } else {
                $this->autocomplete = 'on';
            }
        }
        return $this;
    }

    /**
     * Set attribute novalidate for the form.
     *
     * @return static
     */
    public function novalidate(): static
    {
        $this->novalidate = true;
        return $this;
    }

    /**
     * Set attribute accept-charset for the form.
     *
     * @param string $acceptCharset
     * @return static
     */
    public function acceptCharset(string $acceptCharset): static
    {
        if (!empty(trim($acceptCharset))) {
            $acceptCharset = trim(explode(',', $acceptCharset)[0]);
            Globals::setCharset(strtoupper($acceptCharset));
        }
        return $this;
    }

    /**
     * Initialize CAPTCHA settings.
     *
     * @param string $project
     * @param string $siteKey
     * @param string|null $userProvidedCredentials
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return bool
     */
    public static function captchaInit(
        string $project,
        string $siteKey,
        ?string $userProvidedCredentials = null,
    ): bool {
        try {
            if (!empty(trim($project))) {
                self::$projectId = $project;
            } else {
                FormErrorHandler::exception(
                    "Project is required for CAPTCHA initialization.",
                    400,
                    "fE001"
                );
            }
            if (!empty(trim($siteKey))) {
                self::$sitekey = $siteKey;
            } else {
                FormErrorHandler::exception(
                    "SiteKey is required for CAPTCHA initialization.",
                    400,
                    "fE002"
                );
            }
            $userProvidedCredentials = Globals::toStr($userProvidedCredentials);
            if (!empty(trim($userProvidedCredentials))) {
                self::$userProvidedCredentials = $userProvidedCredentials;
            }
            return true;
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
            return false;
        }
    }
    /**
     * Enable CAPTCHA for the form.
     *
     * @param string|null $action
     * @return static
     */
    public function captcha(?string $action = "GENERAL"): static
    {
        if (empty(trim(self::$sitekey))) {
            $s = Globals::toStr(Config::getWithFallback(
                ['FG_RECAPTCHA_SITEKEY', 'RECAPTCHA_SITEKEY'],
                ""
            ));
            self::$sitekey = $s;
        }

        if (empty(trim(self::$projectId))) {
            $p = Globals::toStr(Config::getWithFallback(
                ['FG_RECAPTCHA_PROJECT_ID', 'RECAPTCHA_PROJECT_ID'],
                ""
            ));

            self::$projectId = $p;
        }
        $this->captchaAction = preg_replace(
            '/[^a-zA-Z0-9_\/]/',
            '',
            $action ?? 'GENERAL'
        ) ?? 'GENERAL';

        if ($this->captchaAction == '') {
            $this->captchaAction = 'GENERAL';
        }

        $this->method = 'POST';
        $this->captcha = true;
        return $this;
    }

    /**
     * Enable refresh protection for the form.
     *
     * @return static
     */
    public function refreshProtection(): static
    {
        $this->refreshProtection = true;
        return $this;
    }

    /**
     * Add raw HTML to the form.
     *
     * @param string $html
     * @return HTML
     */
    public function html(string $html): HTML
    {
        $html = new HTML($html, $this->formID, $this);
        $this->form[] = $html;
        return $html;
    }

    /**
     * Add a label to the form.
     *
     * @param string $label
     * @param string $for
     * @return Label
     */
    public function label(string $label, string $for): Label
    {
        $label = new Label($label, $for);
        $this->form[] = $label;
        return $label;
    }

    /**
     * Add a fieldset to the form.
     *
     * @param string $legend
     * @return Fieldset
     */
    public function fieldset(string $legend = ""): Fieldset
    {
        $fieldset = new Fieldset($legend);
        $this->form[] = $fieldset;
        return $fieldset;
    }

    /**
     * End the current fieldset in the form.
     *
     * @return static
     */
    public function endFieldset(): static
    {
        $this->form[] = new EndFieldset();
        return $this;
    }

    /**
     * Add a legend to the form.
     *
     * @param string $legend
     * @return Legend
     */
    public function legend(string $legend): Legend
    {
        $legend = new Legend($legend);
        $this->form[] = $legend;
        return $legend;
    }

    /**
     * Add a text input to the form.
     *
     * @param string $name
     * @return Text
     */
    public function text(string $name): Text
    {
        $name = $this->formID . '[' . $name . ']';
        $text = new Text($name);
        $this->form[] = $text;
        return $text;
    }

    /**
     * Add a password input to the form.
     *
     * @param string $name
     * @return Password
     */
    public function password(string $name): Password
    {
        $name = $this->formID . '[' . $name . ']';
        $password = new Password($name);
        $this->form[] = $password;
        return $password;
    }

    /**
     * Add a search input to the form.
     *
     * @param string $name
     * @return Search
     */
    public function search(string $name): Search
    {
        $name = $this->formID . '[' . $name . ']';
        $search = new Search($name);
        $this->form[] = $search;
        return $search;
    }

    /**
     * Add an email input to the form.
     *
     * @param string $name
     * @return Email
     */
    public function email(string $name): Email
    {
        $name = $this->formID . '[' . $name . ']';
        $email = new Email($name);
        $this->form[] = $email;
        return $email;
    }

    /**
     * Add a telephone input to the form.
     *
     * @param string $name
     * @return Tel
     */
    public function tel(string $name): Tel
    {
        $name = $this->formID . '[' . $name . ']';
        $tel = new Tel($name);
        $this->form[] = $tel;
        return $tel;
    }

    /**
     * Add a URL input to the form.
     *
     * @param string $name
     * @return Url
     */
    public function url(string $name): Url
    {
        $name = $this->formID . '[' . $name . ']';
        $url = new Url($name);
        $this->form[] = $url;
        return $url;
    }

    /**
     * Add a number input to the form.
     *
     * @param string $name
     * @return Number
     */
    public function number(string $name): Number
    {
        $name = $this->formID . '[' . $name . ']';
        $number = new Number($name);
        $this->form[] = $number;
        return $number;
    }

    /**
     * Add a range input to the form.
     *
     * @param string $name
     * @return Range
     */
    public function range(string $name): Range
    {
        $name = $this->formID . '[' . $name . ']';
        $range = new Range($name);
        $this->form[] = $range;
        return $range;
    }

    /**
     * Add a date input to the form.
     *
     * @param string $name
     * @return Date
     */
    public function date(string $name): Date
    {
        $name = $this->formID . '[' . $name . ']';
        $date = new Date($name);
        $this->form[] = $date;
        return $date;
    }

    /**
     * Add a time input to the form.
     *
     * @param string $name
     * @return Time
     */
    public function time(string $name): Time
    {
        $name = $this->formID . '[' . $name . ']';
        $time = new Time($name);
        $this->form[] = $time;
        return $time;
    }

    /**
     * Add a datetime input to the form.
     *
     * @param string $name
     * @return DateTime
     */
    public function dateTime(string $name): DateTime
    {
        $name = $this->formID . '[' . $name . ']';
        $dateTime = new DateTime($name);
        $this->form[] = $dateTime;
        return $dateTime;
    }

    /**
     * Add a month input to the form.
     *
     * @param string $name
     * @return Month
     */
    public function month(string $name): Month
    {
        $name = $this->formID . '[' . $name . ']';
        $month = new Month($name);
        $this->form[] = $month;
        return $month;
    }

    /**
     * Add a week input to the form.
     *
     * @param string $name
     * @return Week
     */
    public function week(string $name): Week
    {
        $name = $this->formID . '[' . $name . ']';
        $week = new Week($name);
        $this->form[] = $week;
        return $week;
    }

    /**
     * Add a checkbox input to the form.
     *
     * @param string $name
     * @param array<mixed> $value
     * @param array<mixed> $label
     * @param array<mixed> $checked
     * @return static|CheckBox
     */
    public function checkBox(
        string $name,
        array $value = [],
        array $label = [],
        array $checked = []
    ): static|CheckBox {
        try {
            $name = $this->formID . '[' . $name . ']';
            if ($value == []) {
                $checkbox = new CheckBox($name);
                $this->form[] = $checkbox;
                return $checkbox;
            } else {
                $name = $name . "[]";
                if (count($label) != count($value)) {
                    FormErrorHandler::exception(
                        'Label and value array must have the same length in CheckBox',
                        400,
                        'fE003'
                    );
                }
                for ($i = 0; $i < count($value); $i++) {
                    $checkbox = new CheckBox($name);
                    if (in_array($value[$i], $checked) || in_array($label[$i], $checked)) {
                        $checkbox->checked();
                    }
                    $checkbox->label(
                        Globals::toStr($label[$i])
                    )->value(
                        Globals::toStr($value[$i])
                    );
                    $this->form[] = $checkbox;
                }
                return $this;
            }
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
            return $this;
        }
    }

    /**
     * Add a radio input to the form.
     *
     * @param string $name
     * @param array<mixed> $value
     * @param array<mixed> $label
     * @return static|Radio
     */
    public function radio(string $name, array $value = [], array $label = []): static|Radio
    {
        try {
            $name = $this->formID . '[' . $name . ']';
            if ($value == []) {
                $radio = new Radio($name);
                $this->form[] = $radio;
                return $radio;
            } else {
                if ($label == []) {
                    for ($i = 0; $i < count($value); $i++) {
                        $radio = new Radio($name);
                        $radio->value(
                            Globals::toStr($value[$i])
                        )->id(
                            Globals::toStr($value[$i])
                        );
                        $this->form[] = $radio;
                    }
                } else {
                    if (count($label) != count($value)) {
                        FormErrorHandler::exception(
                            'Label and value array must have the same length',
                            400,
                            'fE004'
                        );
                    }
                    for ($i = 0; $i < count($value); $i++) {
                        $radio = new Radio($name);
                        $radio->value(
                            Globals::toStr($value[$i])
                        )->id(
                            Globals::toStr($value[$i])
                        )->label(
                            Globals::toStr($label[$i])
                        );
                        $this->form[] = $radio;
                    }
                }
                return $this;
            }
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
            return $this;
        }
    }

    /**
     * Add a file input to the form.
     *
     * @param string $name
     * @return File
     */
    public function file(string $name): File
    {
        $name = $this->formID . '[' . $name . ']';
        $file = new File($name);
        $this->form[] = $file;
        $this->method = 'POST';
        $this->enctype = "multipart/form-data";
        return $file;
    }

    /**
     * Add a hidden input to the form.
     *
     * @param string $name
     * @return Hidden
     */
    public function hidden(string $name): Hidden
    {
        $name = $this->formID . '[' . $name . ']';
        $hidden = new Hidden($name);
        $this->form[] = $hidden;
        return $hidden;
    }

    /**
     * Add a color input to the form.
     *
     * @param string $name
     * @return Color
     */
    public function color(string $name): Color
    {
        $name = $this->formID . '[' . $name . ']';
        $color = new Color($name);
        $this->form[] = $color;
        return $color;
    }

    /**
     * Add a button input to the form.
     *
     * @param string $name
     * @param string $label
     * @return Button
     */
    public function button(
        string $name,
        string $label = ""
    ): Button {
        $name = $this->formID . '[' . $name . ']';
        $button = new Button($name, $label);
        $this->form[] = $button;
        return $button;
    }

    /**
     * Add an image input to the form.
     *
     * @param string $name
     * @param string $src
     * @param string $alt
     * @return Image
     */
    public function image(string $name, string $src, string $alt): Image
    {
        $name = $this->formID . '[' . $name . ']';
        $image = new Image($name, $src, $alt);
        $this->form[] = $image;
        return $image;
    }

    /**
     * Add a submit input to the form.
     *
     * @param string $value
     * @return Submit
     */
    public function submit(string $value = "Submit"): Submit
    {
        $button = new Submit($value);
        $this->form[] = $button;
        return $button;
    }

    /**
     * Add a reset input to the form.
     *
     * @param string $value
     * @return Reset
     */
    public function reset(string $value = "Reset"): Reset
    {
        $button = new Reset($value);
        $this->form[] = $button;
        return $button;
    }

    /**
     * Add a textarea input to the form.
     *
     * @param string $name
     * @return TextArea
     */
    public function textArea(string $name): TextArea
    {
        $name = $this->formID . '[' . $name . ']';
        $textarea = new TextArea($name);
        $this->form[] = $textarea;
        return $textarea;
    }

    /**
     * Add a select input to the form.
     *
     * @param string $name
     * @return Select
     */
    public function select(string $name): Select
    {
        $name = $this->formID . '[' . $name . ']';
        $select = new Select($name);
        $this->form[] = $select;
        return $select;
    }

    /**
     * Get the form attributes as a string.
     *
     * @return string
     */
    private function getAttributes(): string
    {
        $attributes = [
            'action' => $this->action,
            'method' => $this->method,
            'enctype' => $this->enctype,
            'target' => $this->target,
            'autocomplete' => $this->autocomplete,
            'novalidate' => $this->novalidate,
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->class,
            'style' => $this->style,
            'rel' => $this->rel,
            'accept-charset' => Globals::getCharset()
        ];
        $attributes = $this->atributes($attributes);
        $htmlAttributes = $this->htmlAttributes($attributes);
        return $htmlAttributes;
    }

    /**
     * Generate the form HTML.
     *
     * @return string
     */
    private function getAuto(): string
    {
        try {
            if ($this->captcha) {
                if (empty(self::$sitekey) || empty(self::$projectId)) {
                    FormErrorHandler::exception(
                        'For reCAPTCHA to work properly, you must either configure 
                        FG_RECAPTCHA_PROJECT_ID and FG_RECAPTCHA_SITEKEY in your .env file,
                        or initialize them via Form::captchaInit(projectID, yourSiteKey).',
                        400,
                        'fE005'
                    );
                }
            }

            $htmlAttributes = $this->getAttributes();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                session_regenerate_id(true);
            }

            if (!isset($_SESSION[$this->formID]) || !is_array($_SESSION[$this->formID])) {
                $_SESSION[$this->formID] = [];
            }

            if (!isset($_SESSION[$this->formID]['csrf'])) {
                $_SESSION[$this->formID]['csrf'] = bin2hex(random_bytes(64));
            }



            if ($this->captcha) {
                $this->html = '       
                <form' . Globals::toStr($htmlAttributes) . ' >' .
                    "<input type='hidden' name='" . $this->formID . "[csrf]' value='" .
                    htmlspecialchars(
                        Globals::toStr($_SESSION[$this->formID]['csrf']),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    ) .
                    "'> 
                    <input type='hidden' name='" . $this->formID . "[honeypot]'>
                    <input type='hidden' name='" . $this->formID .
                    "[captcha-action]' value='" . $this->captchaAction . "'>
                    <input type='hidden' name='" . $this->formID .
                    "[g-recaptcha-response]' id='" . $this->formID . "_recaptcha'>
                    <input type='hidden' name='formID' value='{$this->formID}'>";
                if (!empty($this->form)) {
                    foreach ($this->form as $input) {
                        $this->html .= $input->getHTML();
                    }
                }
                $this->html .= '<div id="' . Globals::toStr($this->id) . '_recaptcha_container"></div>
                </form>
                <script>
                    var onloadCallback = function () {
                        grecaptcha.enterprise.render("' . Globals::toStr($this->id) . '_recaptcha_container", {
                            "sitekey": "' . self::$sitekey . '",
                            "action": "' . $this->captchaAction . '",
                            "callback": function(token){
                                document.getElementById("' . $this->formID . '_recaptcha").value = token;
                                
                                var autoTextarea = document.querySelector("textarea.g-recaptcha-response");
                                if(autoTextarea) autoTextarea.remove();
                            }
                        });
                    }

                </script>
                <script 
                    src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit" 
                    async 
                    defer>
                </script>';
            } else {
                $this->html = '<form' . Globals::toStr($htmlAttributes) . ' >'
                    . "<input type='hidden' name='formID' value='{$this->formID}'>";
                if (Globals::getSecurity($this->formID)) {
                    $this->html .= "<input type='hidden' name='" . $this->formID . "[csrf]' value='"
                        . htmlspecialchars(
                            Globals::toStr($_SESSION[$this->formID]['csrf']),
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            Globals::getCharset()
                        )
                        . "'> <input type='hidden' name='" . $this->formID . "[honeypot]'>";
                }
                if (!empty($this->form)) {
                    foreach ($this->form as $input) {
                        $this->html = Globals::toStr($this->html);
                        $this->html .= Globals::toStr($input->getHTML());
                    }
                }
                $this->html .= '</form>';
            }
            if ($this->refreshProtection) {
                $this->html .= '<script> 
                    const form = document.getElementById("' . Globals::toStr($this->id) . '");
                    form.addEventListener("submit", function () {
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    }); 
                </script>';
            }
            return $this->html;
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
            return "";
        }
    }

    /**
     * Output the form HTML.
     *
     * @return void
     */
    public function getForm(): void
    {
        echo $this->getAuto();
    }


    /**
     * Get the form HTML as a string.
     *
     * @return string
     */
    public function getHTML(): string
    {
        return $this->getAuto();
    }


    /**
     * Display the form HTML in a pretty format.
     *
     * @return void
     */
    public function showHTML(): void
    {
        $html = Globals::toStr(
            preg_replace('/\s*\/?>\s*/', ">\n", $this->getAuto())
        );

        $html = preg_replace_callback(
            '/<(label|p|button|input|br|hr|img|option|a)[^>]*>.*?<\/\1>\s*/is',
            function (array $m): string {
                return trim(
                    (string) preg_replace('/\s+/', ' ', $m[0])
                ) . "\n";
            },
            $html
        );


        $void = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];
        $indent = 0;
        $pretty = '';

        foreach (explode("\n", Globals::toStr($html)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^<\/[a-z0-9]+/i', $line)) {
                $indent--;
            }
            $pretty .= str_repeat('  ', $indent) . $line . "\n";
            if (preg_match('/^<([a-z0-9]+)(\s[^>]*)?>$/i', $line, $m) && !in_array(strtolower($m[1]), $void)) {
                $indent++;
            }
        }


        echo '<pre>' . htmlspecialchars($pretty) . '</pre>';
    }


    /**
     * Start the form HTML output.
     *
     * @return void
     */
    public function start(): void
    {
        try {
            if ($this->captcha) {
                if (empty(self::$sitekey) || empty(self::$projectId)) {
                    FormErrorHandler::exception(
                        'For reCAPTCHA to work properly, you must either configure 
                        FG_RECAPTCHA_PROJECT_ID and FG_RECAPTCHA_SITEKEY in your .env file,
                        or initialize them via Form::captchaInit(projectID, yourSiteKey).',
                        400,
                        'fE006'
                    );
                }
            }

            $htmlAttributes = $this->getAttributes();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                session_regenerate_id(true);
            }

            if (!isset($_SESSION[$this->formID]) || !is_array($_SESSION[$this->formID])) {
                $_SESSION[$this->formID] = [];
            }

            if (!isset($_SESSION[$this->formID]['csrf'])) {
                $_SESSION[$this->formID]['csrf'] = bin2hex(random_bytes(64));
            }



            if ($this->captcha) {
                echo '       
                <form' . Globals::toStr($htmlAttributes) . ' >' .
                    "<input type='hidden' name='" . $this->formID . "[csrf]' value='" .
                    htmlspecialchars(
                        Globals::toStr($_SESSION[$this->formID]['csrf']),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        Globals::getCharset()
                    ) .
                    "'> 
                    <input type='hidden' name='" . $this->formID . "[honeypot]'>
                    <input type='hidden' name='" . $this->formID .
                    "[captcha-action]' value='" . $this->captchaAction . "'>
                    <input type='hidden' name='" . $this->formID .
                    "[g-recaptcha-response]' id='" . $this->formID . "_recaptcha'>
                    <input type='hidden' name='formID' value='{$this->formID}'>";
            } else {
                echo '<form' . Globals::toStr($htmlAttributes) . ' >'
                    . "<input type='hidden' name='formID' value='{$this->formID}'>";
                if (Globals::getSecurity($this->formID)) {
                    echo "<input type='hidden' name='" . $this->formID . "[csrf]' value='"
                        . htmlspecialchars(
                            Globals::toStr($_SESSION[$this->formID]['csrf']),
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            Globals::getCharset()
                        )
                        . "'> <input type='hidden' name='" . $this->formID . "[honeypot]'>";
                }
            }
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
        }
    }

    /**
     * End the form HTML output.
     *
     * @return void
     */
    public function end(): void
    {
        try {
            if ($this->captcha) {
                if (empty(self::$sitekey) || empty(self::$projectId)) {
                    FormErrorHandler::exception(
                        'For reCAPTCHA to work properly, you must either configure 
                        FG_RECAPTCHA_PROJECT_ID and FG_RECAPTCHA_SITEKEY in your .env file,
                        or initialize them via Form::captchaInit(projectID, yourSiteKey).',
                        400,
                        'fE007'
                    );
                }
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                session_regenerate_id(true);
            }

            if (!isset($_SESSION['csrf']) || empty($_SESSION['csrf'])) {
                $_SESSION['csrf'] = bin2hex(random_bytes(64));
            }

            if ($this->captcha) {
                echo '<div id="' . Globals::toStr($this->id) . '_recaptcha_container"></div>
                </form>
                <script>
                    var onloadCallback = function () {
                        grecaptcha.enterprise.render("' . Globals::toStr($this->id) . '_recaptcha_container", {
                            "sitekey": "' . self::$sitekey . '",
                            "action": "' . $this->captchaAction . '",
                            "callback": function(token){
                                document.getElementById("' . $this->formID . '_recaptcha").value = token;
                                
                                var autoTextarea = document.querySelector("textarea.g-recaptcha-response");
                                if(autoTextarea) autoTextarea.remove();
                            }
                        });
                    }

                </script>
                <script 
                    src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit" 
                    async 
                    defer>
                </script>';
            } else {
                echo '</form>';
            }
            if ($this->refreshProtection) {
                echo '<script> 
                    const form = document.getElementById("' . Globals::toStr($this->id) . '");
                    form.addEventListener("submit", function () {
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    }); 
                </script>';
            }
        } catch (FormGeneratorException $e) {
            FormErrorHandler::showError($e);
        }
    }

    /**
     * Get form data and validation results.
     *
     * @param string $formID
     * @param array<mixed> $validation
     * @param bool|null $getDevErrors
     * @return ValidationResult|null
     */
    public static function getDataFrom(
        string $formID,
        array $validation = [],
        ?bool $getDevErrors = null
    ): ?ValidationResult {
        if ($getDevErrors === null) {
            $getDevErrors = filter_var(
                Config::getWithFallback(
                    ['FG_DEFAULT_GET_DEV_ERRORS', 'DEFAULT_GET_DEV_ERRORS'],
                    false
                ),
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return FormDataProvider::getDataFrom(
            $formID,
            self::$projectId,
            self::$sitekey,
            $validation,
            self::$userProvidedCredentials,
            $getDevErrors
        );
    }

    /**
     * Creating a form from JSON
     *
     * @param string|array<string,mixed> $json
     * @param bool $returnAsObject
     * @return string|false
     */
    public static function json(
        string|array $json,
        bool $returnAsObject = true
    ): string|false {
        return JsonDataProvider::jsonDecoder(
            $json,
            $returnAsObject
        );
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

    /**
     * Process and escape the attributes for HTML output.
     *
     * @param array<mixed> $attributes
     * @return array<mixed>
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
}
