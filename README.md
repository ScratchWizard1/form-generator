# FormGenerator

Strict-typed PHP library for building, validating and processing HTML forms — cleanly, safely and without messy PHP/HTML mixing.

> Stop echoing long HTML strings. Stop manually parsing `$_POST`. Start writing structured, readable form logic.

---

## 📚 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Why not just use Symfony Forms / Laravel Validation?](#-why-not-just-use-symfony-forms--laravel-validation)
- [Installation & Requirements](#-installation--requirements)
  - [Installation](#installation)
  - [Runtime Requirements](#requirements)
  - [Dependencies](#dependencies)
  - [Development Dependencies](#development-optional)
- [Quick Example](#-quick-example)
- [Form Builder](#-form-builder)
  - [Element Methods Overview](#element-methods-overview)
  - [Special Notes (Button, Image, Raw HTML)](#special-notes)
  - [Supported Attributes](#supported-attributes)
  - [Attribute Methods Overview](#attribute-methods-overview)
  - [Important Notes](#important-notes)
  - [Advanced: setAttributes()](#advanced-setattributes)
- [Receiving & Validating Data](#-receiving--validating-data)
  - [getDataFrom() with formId](#getdatafrom-with-formid)
  - [getDataFrom() without formId](#getdatafrom-without-formid)
  - [getDataFrom() without rules](#getdatafrom-without-rules)
  - [Validation Rules](#validation-rules-all-supported-rules)
  - [Special Behavior Rules](#special-behavior-rules)
  - [Tel Rule Behavior](#tel-rule-behavior)
  - [Format Rule](#format-rule)
  - [Extension Group Behavior](#extension-group-behavior)
  - [Modifiers](#modifiers)
  - [Validation Configuration (\_\_CONFIG\_\_)](#️-validation-configuration-__config__)
- [Standalone Validation](#-standalone-validation)
- [File Validation](#-file-validation)
- [Google reCAPTCHA Enterprise](#-google-recaptcha-enterprise)
- [Environment Variables (.env)](#-environment-variables-env)
- [Architecture](#-architecture)
- [Development & Quality](#-development--quality)
- [Project Status](#-project-status)
- [Contributing](#-contributing)
- [Security](#-security)
- [License](#-license)

---

## 📌 Overview

Working with HTML forms in plain PHP often results in:

- Mixing PHP and HTML repeatedly  
- Echoing long strings with escaped quotes  
- Manually validating `$_POST` and `$_FILES`  
- Scattered validation logic  
- Inconsistent file handling  

**FormGenerator** provides a structured and modern alternative for pure PHP projects.

---

## ✨ Features

- Fluent HTML form builder (clean, chainable API)
- Unified data extraction + validation
- Standalone validation engine (usable without form builder)
- Automatic CSRF token protection
- Built-in honeypot anti-spam protection
- Automatic input sanitization (escaping + strip_tags by default)
- File upload validation (size, MIME type, extension — no storage)
- Google reCAPTCHA Enterprise support
- Optional `.env` configuration support
- libphonenumber integration for phone validation
- Strict types (`declare(strict_types=1);`)
- PSR-12 compliant
- PHPStan level: max
- ~4000 tests
- ~88% test coverage

---
## 🤔 Why not just use Symfony Forms / Laravel Validation?

- No framework dependency
- Pure PHP
- Zero service container
- Lightweight

---

## 📦 Installation & Requirements

### Requirements
- PHP >= 8.0 (tested on 8.3 and 8.4)
- ext-json
- Composer

### Installation

**1. Create a folder for your new project and initialize Composer**
```bash
composer init
```

**2. Install the library**
```bash
composer require scratchwizard1/form-generator
```

**3. In the root of your project, create `index.php` and load the autoloader**
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use FormGenerator\Form;
```
> The library is ready to use 🎉

### Dependencies

The following packages are installed automatically via Composer:

- google/cloud-recaptcha-enterprise ^2.1
- vlucas/phpdotenv ^5.5
- giggsey/libphonenumber-for-php ^9.0

### Development (optional)

If you want to contribute or run tests:

- phpunit/phpunit ^12.0
- squizlabs/php_codesniffer ^4.0
- phpstan/phpstan ^2.0

Install dev dependencies:

```bash
composer install
```

---

## 🚀 Quick Example

Below is a minimal example showing how a basic form can be defined using FormGenerator.

```php
use FormGenerator\Form;

$form = Form::new("index.php", "contact-form");

$form->fieldset()
     ->class("fieldset-class")
     ->name("main");

$form->legend("Contact us");

$form->text("name")
     ->placeholder("Your name")
     ->minlength(3)
     ->required();

$form->email("email")
     ->placeholder("Your email")
     ->required();

$form->submit("Send");

$form->endFieldset();

$form->getForm(); // renders the form directly
```

### Generated HTML

The above definition produces clean, structured HTML:

```html
<form action="index.php" method="POST" enctype="application/x-www-form-urlencoded" target="_self" autocomplete="on" id="contact-form" accept-charset="UTF-8">
    <input type="hidden" name="formID" value="contact-form">
    <input type="hidden" name="contact-form[csrf]" value="...">
    <input type="hidden" name="contact-form[honeypot]">

    <fieldset name="main" class="fieldset-class">
        <legend>Contact us</legend>

        <input type="text"
               name="contact-form[name]"
               id="contact-form_name"
               placeholder="Your name"
               minlength="3"
               autocomplete="on"
               required>

        <input type="email"
               name="contact-form[email]"
               id="contact-form_email"
               placeholder="Your email"
               autocomplete="on"
               required>

        <input type="submit" id="Send" value="Send">
    </fieldset>
</form>
```

### What happens automatically?

When using the form builder:

- CSRF protection is automatically added
- Honeypot anti-spam field is generated
- Form ID namespacing is applied
- Inputs are structured consistently
- Secure defaults are enforced

No manual CSRF handling or hidden input management is required.

---

## 🧱 Form Builder

### Element Methods Overview

All element methods are chainable and allow attribute configuration after creation.

```php
$form->text("username")->required()->class("input");
```

| Method | Parameters | Description |
|--------|------------|-------------|
| `text($name)` | string `$name` | Creates `<input type="text">` |
| `email($name)` | string `$name` | Creates `<input type="email">` |
| `password($name)` | string `$name` | Creates `<input type="password">` |
| `number($name)` | string `$name` | Creates `<input type="number">` |
| `tel($name)` | string `$name` | Creates `<input type="tel">` |
| `url($name)` | string `$name` | Creates `<input type="url">` |
| `search($name)` | string `$name` | Creates `<input type="search">` |
| `date($name)` | string `$name` | Creates `<input type="date">` |
| `datetime($name)` | string `$name` | Creates `<input type="datetime-local">` |
| `time($name)` | string `$name` | Creates `<input type="time">` |
| `month($name)` | string `$name` | Creates `<input type="month">` |
| `week($name)` | string `$name` | Creates `<input type="week">` |
| `range($name)` | string `$name` | Creates `<input type="range">` |
| `color($name)` | string `$name` | Creates `<input type="color">` |
| `checkbox($name)` | string `$name` | Creates `<input type="checkbox">` |
| `radio($name)` | string `$name` | Creates `<input type="radio">` |
| `file($name)` | string `$name` | Creates `<input type="file">` |
| `hidden($name)` | string `$name` | Creates `<input type="hidden">` |
| `textarea($name)` | string `$name` | Creates `<textarea>` |
| `select($name)` | string `$name` | Creates `<select>` |
| `button($name, $content)` | string `$name`, string `$content` | Creates `<button>` |
| `submit($value)` | string `$value` | Creates `<input type="submit">` |
| `reset($value)` | string `$value` | Creates `<input type="reset">` |
| `image($name, $src, $alt)` | string `$name`, string `$src`, string `$alt` | Creates `<input type="image">` |
| `label($text)` | string `$text` | Creates `<label>` |
| `legend($text)` | string `$text` | Creates `<legend>` |
| `fieldset()` | — | Opens `<fieldset>` |
| `endFieldset()` | — | Closes `<fieldset>` |
| `html($html)` | string `$html` | Inserts raw HTML |

---

### Special Notes

#### Button

```php
$form->button("myBtn", "Click Me");
```

- `$name` → name attribute  
- `$content` → inner HTML of `<button>`

#### Image Input

```php
$form->image("submitImage", "/img/send.png", "Send");
```

- `$name` → name attribute  
- `$src` → image source  
- `$alt` → alternative text  

#### Raw HTML

```php
$form->html("<p>Custom content</p>")->class("box");
```

- Accepts a valid HTML string including `< >`
- Supports attribute chaining

---

### Supported Attributes

All elements share a unified attribute API. Each attribute has a corresponding method. All methods are chainable.

### Attribute Methods Overview

| Attribute | Method | Parameter Type | Notes |
|------------|--------|----------------|-------|
| id | `->id($value)` | string | Sets element ID |
| name | `->name($value)` | string | Overrides generated name |
| class | `->class($value)` | string | CSS classes |
| style | `->style($value)` | string | Inline styles |
| value | `->value($value)` | string | Input value |
| placeholder | `->placeholder($value)` | string | Placeholder text |
| title | `->title($value)` | string | Tooltip text |
| dir | `->dir($value)` | string | Text direction |
| onclick | `->onclick($value)` | string | JS handler |
| pattern | `->pattern($value)` | string | Regex validation |
| list | `->list($value)` | string | Datalist ID |
| form | `->form($value)` | string | External form reference |
| accept | `->accept($value)` | string | File types |
| src | `->src($value)` | string | Image source |
| alt | `->alt($value)` | string | Alternative text |
| wrap | `->wrap($value)` | string | Textarea wrapping |
| autocomplete | `->autocomplete($value)` | string | Default: `"on"` |

#### Numeric / Length Attributes

| Attribute | Method | Parameter Type |
|------------|--------|----------------|
| minlength | `->minlength($value)` | int |
| maxlength | `->maxlength($value)` | int |
| size | `->size($value)` | int |
| min | `->min($value)` | int\|float\|string |
| max | `->max($value)` | int\|float\|string |
| step | `->step($value)` | int\|float\|string (default: `"any"`) |
| rows | `->rows($value)` | int |
| cols | `->cols($value)` | int |
| width | `->width($value)` | int |
| height | `->height($value)` | int |

#### Boolean Attributes

These attributes do not require parameters.

| Attribute | Method |
|------------|--------|
| required | `->required()` |
| disabled | `->disabled()` |
| readonly | `->readonly()` |
| hidden | `->hidden()` |
| autofocus | `->autofocus()` |
| multiple | `->multiple()` |
| checked | `->checked()` |
| selected | `->selected()` |

---

### Important Notes

#### 1️⃣ Null Values

Although attributes are internally stored as nullable properties,  
**public API methods do not accept `null` as a valid value.**

If a value is not provided, the attribute is simply not rendered.

#### 2️⃣ Attribute Compatibility

If an attribute does not apply to a specific element, it is automatically ignored.

```php
$form->text("username")->min(5);
```

Since `min()` applies only to numeric/date inputs, it will not be rendered.

#### 3️⃣ Clean HTML Guarantee

The form builder ensures:

- No empty attributes
- No invalid attributes
- No duplicated attributes
- Only relevant attributes are rendered

This guarantees valid and clean HTML output.

---

### Advanced: `setAttributes()`

The `setAttributes()` method is available on **every element instance created by the builder**.

This includes:

- All input elements (`text()`, `email()`, `number()`, etc.)
- Structural elements (`fieldset()`, `legend()`, `label()`)
- Action elements (`button()`, `submit()`, `reset()`)
- Select and textarea
- Raw HTML elements created via `html()`

> Any component added through a builder method supports `setAttributes()`.

#### Usage

```php
$form->text("username")
     ->setAttributes([
         'data-test' => 'example',
         'aria-label' => 'Username field'
     ]);
```

#### Priority Rules

`setAttributes()` has **higher priority** than fluent attribute methods.

If the same attribute is defined in both places:

```php
$form->text("username")
     ->id("secondaryId")
     ->setAttributes(['id' => 'primaryId']);
```

The final rendered output will be:

```html
id="primaryId"
```

✔ Values defined in `setAttributes()` override values defined via fluent methods.

#### Key Rules

- Keys **must always be strings**
- The key represents the attribute name
- The value represents the attribute value
- Keys may be **any valid HTML attribute name**
- Custom attributes are fully supported:
  - `data-*`
  - `aria-*`
  - Standard attributes (`id`, `class`, `style`, etc.)
  - Non-standard attributes (if valid in HTML context)

Examples of valid keys:

```php
'data-user' => '123'
'aria-label' => 'Form'
'custom-attribute' => 'value'
```

#### Value Handling Rules

| Value Type | Rendering Result |
|------------|------------------|
| `string` | Rendered as `attribute="value"` |
| `int` / `float` | Rendered as `attribute="number"` |
| `true` | Rendered as a boolean attribute (e.g. `required`) |
| `'true'` (string) | Rendered as `attribute="true"` |
| `false` / `null` | Attribute is not rendered |
| `array` (for `class` / `style`) | Values are merged automatically |

##### Boolean Example

```php
$form->text("username")
     ->setAttributes(['required' => true]);
```

Output: `required`

```php
$form->text("username")
     ->setAttributes(['required' => 'true']);
```

Output: `required="true"`

#### When to Use `setAttributes()`

Use `setAttributes()` when:

- You need full control over the rendered attributes
- You want to add custom attributes
- You want to override fluent method values
- You are dynamically generating attribute sets
- You are working with `data-*` or `aria-*` attributes

#### Clean HTML Guarantee

When using `setAttributes()`:

- Duplicate attributes are not rendered multiple times (last value wins)
- Attribute keys are always rendered as HTML attributes (even custom ones)
- Empty string values are rendered as empty attributes (e.g. `attribute=""`)
- Boolean `true` renders attribute without value (e.g. `required`)
- Attribute precedence is respected (`setAttributes()` overrides fluent methods)
- HTML output remains valid and well-formed

> `setAttributes()` always renders the attributes you specify in a valid HTML form.

---

## 📥 Receiving & Validating Data

FormGenerator automatically extracts and validates submitted form data.

> **Note:** `getDataFrom()` receives data directly from the form submission — even fields that fail validation are included in the output. Rules array is optional — if omitted, all submitted data is returned without any validation.

---

### `getDataFrom()` with `formId`

When a `formId` is provided, only data from that specific form is processed. All other submitted data is ignored.
```php
use FormGenerator\Form;

$result = Form::getDataFrom("contact-form", [
    'name'  => 'string;required;minlength:3',
    'email' => 'email;required'
]);

if ($result->isValid()) {
    $data = $result->getData(); // associative array of sanitized values
} else {
    $error = $result->getError(); // returns one error at a time
}
```

---

### `getDataFrom()` without `formId`

When no `formId` is provided, the function automatically reads it from a hidden input (`<input type="hidden" name="formID">`), which FormGenerator adds to every form automatically.
```php
use FormGenerator\Form;

$result = Form::getDataFrom("", [
    'name'  => 'string;required;minlength:3',
    'email' => 'email;required'
]);

if ($result->isValid()) {
    $data = $result->getData(); // associative array of sanitized values
} else {
    $error = $result->getError(); // returns one error at a time
}
```

---

### `getDataFrom()` without rules

If you omit the rules array, the function simply collects and returns all submitted data without any validation.
```php
use FormGenerator\Form;

$result = Form::getDataFrom("contact-form");

$data = $result->getData(); // all submitted data, no validation applied
```

#### Example Outputs

**Valid Submission (`->getData()`):**

```php
[
    'name'  => 'Jane Doe',
    'email' => 'jane@example.com'
]
```

**Invalid Submission (`->getError()`):**

```php
[
    'message' => "Validation error for key 'name': The value has length 2 and is below the allowed minimum.",
    'code'  => 422,
    'id'    => 'vE084',
    'file'  => '/var/www/html/index.php',
    'line'  => 42
]
```

> Each error always has a unique `id` and code for consistent handling. Only one error is returned at a time.

---

### Validation Rules (All Supported Rules)

> **Value column legend**
> - `—` = rule does NOT require a value  
> - `✔ required` = rule requires a value (`rule:value`)  
> - `optional` = value is optional  
> - `special` = special behavior (no value after colon)

| Rule | Aliases | Value | Description | Example |
|------|----------|--------|-------------|----------|
| Alpha | `a`, `al`, `alpha` | — | Letters only | `alpha;` |
| AlphaNumber | `an`, `aln`, `alphan`, `alnum`, `alphanum`, `alphanumber` | — | Letters and numbers only | `alphaNumber;` |
| Boolean | `b`, `bool`, `boolean` | — | Must be a boolean value | `boolean;` |
| Color | `c`, `color` | — | Valid color value | `color;` |
| Date | `d`, `date` | — | Valid date | `date;` |
| DateTime | `dt`, `datetime` | — | Valid datetime | `dateTime;` |
| Email | `e`, `mail`, `email` | — | Valid email address | `email;` |
| Enum | `in`, `en`, `enum` | ✔ required | Must be one of the allowed values | `enum:option1,option2;` |
| Nenum | `nin`, `nen`, `nenum` | ✔ required | Must NOT be one of the allowed values | `nenum:option1,option2;` |
| Equal | `eq`, `equal` | ✔ required | Must equal the given value | `equal:value;` |
| Nequal | `neq`, `nequal` | ✔ required | Must NOT equal the given value | `nequal:value;` |
| Extension | `ex`, `exten`, `extension` | ✔ required | File extension validation with group normalization (e.g. `jpg` also allows `jpeg`, `jpe`). Leading dots are ignored. Multiple extensions can be specified separated by comma (`,`). | `extension:jpg,png;` |
| Mime | `mim`, `mime` | ✔ required | MIME type validation | `mime:image/png;` |
| File | `f`, `file` | — | File input validation | `file;` |
| Format | `fo`, `for`, `form`, `format` | ✔ required | Format validation | `format:int` |
| Ip | `i`, `ip` | — | Valid IP address | `ip;` |
| Json | `j`, `json` | — | Valid JSON | `json;` |
| Length | `l`, `len`, `length` | ✔ required | Exact length | `length:5;` |
| Max | `ma`, `max` | ✔ required | Maximum numeric/date/time value | `max:100;` |
| Min | `mi`, `min` | ✔ required | Minimum numeric/date/time value | `min:10;` |
| MaxLength | `mal`, `malen`, `malength`, `maxl`, `maxlen`, `maxlength` | ✔ required | Maximum string length | `maxLength:10;` |
| MinLength | `mil`, `milen`, `milength`, `minl`, `minlen`, `minlength` | ✔ required | Minimum string length | `minLength:5;` |
| MaxWords | `maw`, `maword`, `mawords`, `maxw`, `maxword`, `maxwords` | ✔ required | Maximum word count | `maxWords:5;` |
| MinWords | `miw`, `miword`, `miwords`, `minw`, `minword`, `minwords` | ✔ required | Minimum word count | `minWords:3;` |
| Month | `m`, `month` | — | Valid month | `month;` |
| Number | `n`, `num`, `number` | — | Numeric value | `number;` |
| Null | `null` | special | If value is empty, adds `null` | `null;` |
| NotNull | `nnull`, `notnull` | special | If value is empty, adds empty string `""` | `notnull;` |
| Ignore | `ignore` | — | Field is completely ignored (not added) | `ignore;` |
| Password | `p`, `pas`, `pass`, `password` | — | Password validation | `password;` |
| Regex | `re`, `reg`, `regex` | ✔ required | Must match regular expression | `regex:/^[a-z0-9]+$/i;` |
| Required | `r`, `req`, `required` | — | Field is required | `required;` |
| Size | `si`, `size` | ✔ required | File size validation (units `B, KB, MB, GB, TB` default unit is `MB` when only number is provided) | `size:5;` (=> 5MB) |
| StringRule | `s`, `str`, `string` | — | String validation | `string;` |
| Tel | `te`, `tel` | optional | Valid phone number (optionally restricted to ISO 3166-1 alpha-2 country codes) | `tel;` or `tel:SK,CZ;` |
| Time | `ti`, `time` | — | Valid time | `time;` |
| Url | `u`, `url` | — | Valid URL | `url;` |
| Week | `w`, `week` | — | Valid week | `week;` |

---

### Special Behavior Rules

- If a field has **no value** and:
  - neither `null` nor `notnull` is present → the field is **not added**
  - `null` is present → the field is added with value `null`
  - `notnull` is present → the field is added with empty string `""`
  - `ignore` is present → the field is completely ignored

---

### Tel Rule Behavior

- If no country codes are provided → fallback regions are loaded from the `.env` file.
- If no fallback is configured → all supported regions are used.
- If country codes are provided → they must be valid ISO 3166-1 alpha-2 codes.
- On success → number is returned in international format.

Example `.env` key:

```env
FG_PHONE_FALLBACK_REGIONS=SK,CZ
```

---

### Format Rule

The `format` rule is used to **convert the final validated value into a specific type**.

It does not validate structure — it transforms the output value after validation.

#### Syntax

```
format:<type>[:value]
```

- `format` → format rule  
- `<type>` → target format type  
- `[:value]` → optional parameter (used only for some types)

Example:

```
format:round:2
```

This means: convert value to float and round it to 2 decimal places.

#### Supported Format Types

| Format Type | Description |
|-------------|-------------|
| `round` | Rounds numeric value. Optional precision (default is `0`). |
| `integer` | Converts value to integer. |
| `float` | Converts value to float. |
| `string` | Converts value to string. |
| `datetime` | Converts value to `DateTime` object. |
| `boolean` | Converts value to boolean. |

#### Format Behavior Details

**`round`** — Converts value to float. Rounds using optional precision parameter. Default precision is `0`.

```
format:round
format:round:2
```

**`integer`** — Converts value to integer. Throws an error if value exceeds integer limits.

```
format:integer
```

**`float`** — Converts value to float. Throws an error for invalid, infinite, or NaN values.

```
format:float
```

**`string`** — Converts value to string. Arrays are converted to string representation (except file inputs).

```
format:string
```

**`datetime`** — Converts value to a `DateTime` object.

Supported input formats:

- Unix timestamp
- `Y-m-d`
- `Y-m`
- `Y`
- `H:i`
- `H:i:s`
- ISO 8601 (`Y-m-dTH:i:sP`)
- ISO 8601 without timezone (`Y-m-dTH:i:s`)
- ISO week format (`YYYY-Www`)

```
format:datetime
```

**`boolean`** — Converts value using PHP `FILTER_VALIDATE_BOOLEAN`.

Accepted values include: `true`, `false`, `1`, `0`, `on`, `off`, `yes`, `no`.

```
format:boolean
```

#### Important Rules

- If the format type is missing → an error is thrown.
- If an unsupported format type is used → an error is thrown.
- File inputs are never reformatted.
- Arrays are returned unchanged (except when converted to string).

---

### Extension Group Behavior

File extensions are **normalized into groups** so that common variants are automatically accepted. If you specify one extension from a group, all grouped variants are allowed.

#### Supported Extension Groups (Automatic Normalization)

- `jpg` → also allows `jpeg`, `jpe`
- `png` → also allows `apng`
- `svg` → also allows `svgz`
- `tif` → also allows `tiff`
- `ico` → also allows `cur`
- `doc` → also allows `dot`
- `xls` → also allows `xlt`
- `ppt` → also allows `pot`, `pps`
- `docx` → also allows `dotx`, `docm`, `dotm`
- `xlsx` → also allows `xltx`, `xlsm`, `xltm`
- `pptx` → also allows `potx`, `ppsx`, `pptm`, `potm`, `ppsm`
- `odt` → also allows `ott`
- `ods` → also allows `ots`
- `odp` → also allows `otp`
- `odg` → also allows `otg`
- `txt` → also allows `text`, `conf`, `cfg`
- `md` → also allows `markdown`
- `htm` → also allows `html`
- `js` → also allows `mjs`, `cjs`
- `mp3` → also allows `mpga`
- `ogg` → also allows `oga`
- `mp4` → also allows `m4v`
- `gz` → also allows `gzip`
- `azw` → also allows `azw3`

---

### Modifiers

Modifiers change how validation behaves or how the result is returned.

| Modifier | Aliases |
|----------|---------|
| array | `a`, `ar`, `array` |
| any | `an`, `any` |
| cut | `c`, `cut` |
| domain | `d`, `domain` |
| exist | `e`, `exist` |
| hash | `h`, `hash` |
| only | `o`, `only` |
| strict | `st`, `strict` |
| v6 | `v6` |

#### Tests and Supported Modifiers

| Test | Modifier | Behavior |
|------|----------|----------|
| alpha | `o` | Returns only alphabetic characters (filters out everything else). |
| alphaNumber | `o` | Returns only alphanumeric characters. |
| email | `d` | Additionally checks whether the email domain exists (DNS check). |
| enum | `o` | Only allowed values may appear in the input. |
| enum | `a` | Input is treated as array and must contain **all defined allowed values**. |
| enum | `an` | Input is treated as array and must contain **at least one allowed value**. |
| extension | `st` | Disables extension group normalization. Only explicitly defined extensions are allowed (no automatic variants such as `jpg` → `jpeg`, `jpe`). |
| ip | `v6` | Validates IPv6 format only. |
| json | `ar` | Validates JSON and ensures decoded value is an array. |
| length | `c` | Cuts value to defined length if it exceeds limit. |
| maxLength | `c` | Cuts value to maximum length if it exceeds limit. |
| minLength | `c` | Pads value (with spaces) until minimum length is reached. |
| nenum | `o` | None of the disallowed values may appear in the input. |
| nenum | `a` | Input is treated as array and must NOT contain any disallowed values. |
| nenum | `an` | Input is treated as array and none of the values may match the forbidden list. |
| number | `i` | Validates that value is an integer number (numeric integer format required). |
| number | `f` | Validates that value is a float number (decimal format allowed). |
| number | `o` | Returns only numeric characters from input (filters everything else). Result is returned as string. |
| number | `o,i` | Returns only numeric characters from input. Result remains a string (convertible to integer). |
| number | `o,f` | Returns only numeric characters (including decimal separator). Result remains a string (convertible to float). |
| password | `h` | Returns hashed password using `password_hash()`. |
| url | `e` | Validates that the URL exists (HTTP check). |

#### Modifier Explanation

- Modifiers **change validation behavior** (e.g., filtering output, rounding numbers, type conversion).
- If a modifier is used that is **not supported by the test**, validation fails with an error.
- Some modifiers **return modified output** instead of just true/false (e.g., `only`, `hash`).
- Modifiers that do nothing for a given test are **ignored** if supported.
- Multiple modifiers can be combined where supported (order does not matter).
- If a modifier is provided but the test does not support it → validation error is thrown.

---

## ⚙️ Validation Configuration (`__CONFIG__`)

The special `__CONFIG__` key configures sanitization applied to values in this validation context.

```php
$result = Form::getDataFrom("contact-form", [
    '__CONFIG__' => 'strip_tags:true; htmlspecialchars:true',
    'name'       => 'string;required',
    'email'      => 'email;required'
]);
```

| Key | Aliases | Description | Values | Default |
|-----|---------|-------------|---------|----------|
| strip_tags | `s`, `st`, `strip`, `striptags`, `strip_tags` | Enables or disables `strip_tags` sanitization (removes HTML tags) | `true`, `t` / `false`, `f` | `true` |
| htmlspecialchars | `h`, `html`, `htmlspecial`, `htmlspecialchars` | Enables or disables HTML special character escaping | `true`, `t` / `false`, `f` | `true` |

### Example Configurations

```php
'__CONFIG__' => 's:t; h:t'   // enable both strip_tags and htmlspecialchars (default)
'__CONFIG__' => 's:f; h:f' // disable both (raw values)
'__CONFIG__' => 's:t; h:f'   // only strip tags, no HTML escaping
```

---

## 🔎 Standalone Validation

Validation engine can be used independently from form builder.
All validation rules and behaviors apply in the same way as described in **📥 Receiving & Validating Data** section.

```php
use FormGenerator\Validation;

$data = [
    'username' => 'janedoe',
    'email'    => 'jane@example.com',
    'age'      => '25'
];

$result = Validation::validate($data, [
    'username' => ['string', 'req', 'minl:3', 'maxl:20'],
    'email'    => 'email;req',
    'age'      => ['number:i', 'min:18', 'max:100']
], true);

if ($result->isValid()) {
    $validData = $result->getData();
} else {
    $errors = $result->getError();
}
```

---

## 🛡 File Validation

FormGenerator does **not** store uploaded files.

It validates:

- File exists
- Extension
- MIME type
- Maximum size
- Upload status

After validation, you are responsible for storing files.

---

## 🔐 Google reCAPTCHA Enterprise

Integration with **Google reCAPTCHA Enterprise** is handled via the `google/cloud-recaptcha-enterprise` package.

---

### Setup

1. Create a project in [Google Cloud Console](https://console.cloud.google.com)
2. Enable the **reCAPTCHA Enterprise** API
3. Generate and download a service account credentials file (JSON)
4. Place it in your project, e.g. `/config/google-recaptcha.json`
5. Initialize the integration using one of the following approaches:

**Option A — via `captchaInit()`:**
```php
Form::captchaInit(
    'your-project-id',
    'your-site-key',
    '/config/google-recaptcha.json' // path to the JSON credentials file
);
```

**Option B — via `.env` file (no code required):**
```env
FG_RECAPTCHA_PROJECT_ID=your-project-id
FG_RECAPTCHA_SITEKEY=your-site-key
FG_GOOGLE_APPLICATION_CREDENTIALS=/config/google-recaptcha.json
```

> When all three variables are defined in `.env`, calling `captchaInit()` is not necessary.

---

> **Note:** Unlike standard reCAPTCHA, Google reCAPTCHA Enterprise does not use a secret key. Instead, it authenticates via a service account credentials file.

---

### Credentials Resolution Order

The library resolves the credentials file in the following order:

1. The third parameter passed to `captchaInit()`
2. The `FG_GOOGLE_APPLICATION_CREDENTIALS` environment variable defined in `.env`
3. The default fallback path: `/config/google-recaptcha.json`

> All paths are resolved relative to the project root directory.

---

## 🌱 Environment Variables (.env)

The library optionally supports `.env` files via `vlucas/phpdotenv`. However, it works without `.env` as well — all variables can be set as standard environment variables or configured directly in code.

Below is a full list of supported environment variables:
```env
# Developer error handling
FG_DEFAULT_GET_DEV_ERRORS=true
FG_DEFAULT_SHOW_DEV_ERRORS=true

# Phone validation
FG_PHONE_FALLBACK_REGIONS=SK,CZ

# Google reCAPTCHA Enterprise
FG_GOOGLE_APPLICATION_CREDENTIALS=/config/google-recaptcha.json
FG_RECAPTCHA_PROJECT_ID=your-project-id
FG_RECAPTCHA_SITEKEY=your-site-key
```

---

### Variable Reference

**`FG_DEFAULT_GET_DEV_ERRORS`**
Globally enables or disables developer error messages across the entire library. When set to `false`, no dev errors will be returned from any function, regardless of other settings. Each function also accepts a local override for this setting — the local value always takes precedence over the global one.

**`FG_DEFAULT_SHOW_DEV_ERRORS`**
Controls how developer error messages are delivered globally. When set to `true`, errors are printed directly on the page. When set to `false`, they are only returned as a return value from the respective function.

> **Example:** If `FG_DEFAULT_GET_DEV_ERRORS=true` is set globally but a specific function call has the local override set to `false`, that function will behave as if dev errors are disabled, while all other functions remain unaffected.

**`FG_PHONE_FALLBACK_REGIONS`**
Defines the fallback region(s) used during phone number validation when no region is explicitly provided. Accepts a comma-separated list of ISO 3166-1 alpha-2 country codes (e.g. `SK,CZ`). See [Tel Rule Behavior](#tel-rule-behavior) for details.

**`FG_GOOGLE_APPLICATION_CREDENTIALS`**
Path to the Google service account credentials JSON file used for reCAPTCHA Enterprise authentication. The path is resolved relative to the project root. See [Credentials Resolution Order](#credentials-resolution-order) for details.

**`FG_RECAPTCHA_PROJECT_ID`**
Your Google Cloud project ID used for reCAPTCHA Enterprise. When defined together with `FG_RECAPTCHA_SITEKEY`, calling `captchaInit()` is not required. See [Google reCAPTCHA Enterprise](#-google-recaptcha-enterprise) for details.

**`FG_RECAPTCHA_SITEKEY`**
Your reCAPTCHA Enterprise site key. When defined together with `FG_RECAPTCHA_PROJECT_ID`, calling `captchaInit()` is not required. See [Google reCAPTCHA Enterprise](#-google-recaptcha-enterprise) for details.

---

## 🏛 Architecture

- Static entry point: `Form::new('action','formId')`
- Also supports: `new Form('action','formId')`
- Modular validation engine
- No framework dependency
- Pure PHP implementation

---

## 🧪 Development & Quality

Dev dependencies:

- PHPUnit ^12
- PHPStan ^2 (level max)
- PHP_CodeSniffer ^4 (PSR-12)

Code quality standards:

- `declare(strict_types=1);`
- PSR-12 coding style
- Static analysis at maximum level

---

## 📌 Project Status

- Actively developed
- Originally created as graduation project
- Designed with production usage in mind

---

## 🤝 Contributing

Pull requests are welcome.

Please:

- Follow PSR-12
- Keep strict types
- Add tests for new features

---

## 🔒 Security

If you discover a vulnerability, please open a private issue on GitHub.

---

## 📄 License

MIT License

See `LICENSE` file for details.
