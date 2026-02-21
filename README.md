# FormGenerator

Strict-typed PHP library for building, validating and processing HTML forms — cleanly, safely and without messy PHP/HTML mixing.

> Stop echoing long HTML strings. Stop manually parsing `$_POST`. Start writing structured, readable form logic.

---

## 📌 Overview

Working with HTML forms in plain PHP often results in:

- Mixing PHP and HTML repeatedly  
- Echoing long strings with escaped quotes  
- Manually validating `$_POST` and `$_FILES`  
- Scattered validation logic  
- Inconsistent file handling  

**FormGenerator** provides a structured and modern alternative for pure PHP projects.

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

## 📦 Installation

```bash
composer require scratchwizard/form-generator
```

---

## ⚙️ Requirements

### Runtime

- PHP >= 8.0 (tested on 8.3 and 8.4)
- ext-json
- Composer

### Dependencies

The following packages are installed automatically via Composer:

- google/cloud-recaptcha-enterprise ^2.1
- vlucas/phpdotenv ^5.5
- giggsey/libphonenumber-for-php ^9.0

---

## 🛠 Development (optional)

If you want to contribute or run tests:

- phpunit/phpunit ^12.0
- squizlabs/php_codesniffer ^4.0
- phpstan/phpstan ^2.0

Install dev dependencies:

```bash
composer install
```

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

## 🧱 Element Methods Overview

All element methods are chainable and allow attribute configuration after creation.

Example:

```php
$form->text("username")->required()->class("input");
```

---

### 📋 Method Parameters

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

## 🧩 Special Notes

### Button

```php
$form->button("myBtn", "Click Me");
```

- `$name` → name attribute  
- `$content` → inner HTML of `<button>`

---

### Image Input

```php
$form->image("submitImage", "/img/send.png", "Send");
```

- `$name` → name attribute  
- `$src` → image source  
- `$alt` → alternative text  

---

### Raw HTML

```php
$form->html("<p>Custom content</p>")->class("box");
```

- Accepts a valid HTML string including `< >`
- Supports attribute chaining

---

## 🎛 Supported Attributes

All elements share a unified attribute API.  
Each attribute has a corresponding method.

All methods are chainable.

---

## 📋 Attribute Methods Overview

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

---

### 🔢 Numeric / Length Attributes

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

---

### ✅ Boolean Attributes

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

## ⚠ Important Notes

### 1️⃣ Null Values

Although attributes are internally stored as nullable properties,  
**public API methods do not accept `null` as a valid value.**

If a value is not provided, the attribute is simply not rendered.

---

### 2️⃣ Attribute Compatibility

If an attribute does not apply to a specific element, it is automatically ignored.

Example:

```php
$form->text("username")->min(5);
```

Since `min()` applies only to numeric/date inputs, it will not be rendered.

---

### 3️⃣ Clean HTML Guarantee

The form builder ensures:

- No empty attributes
- No invalid attributes
- No duplicated attributes
- Only relevant attributes are rendered

This guarantees valid and clean HTML output.

---

## 🧩 Advanced: `setAttributes()`

The `setAttributes()` method is available on **every element instance created by the builder**.

This includes:

- All input elements (`text()`, `email()`, `number()`, etc.)
- Structural elements (`fieldset()`, `legend()`, `label()`)
- Action elements (`button()`, `submit()`, `reset()`)
- Select and textarea
- Raw HTML elements created via `html()`

In short:

> Any component added through a builder method supports `setAttributes()`.

---

### Usage

```php
$form->text("username")
     ->setAttributes([
         'data-test' => 'example',
         'aria-label' => 'Username field'
     ]);
```

---

## 🔝 Priority Rules

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

---

## 🗝 Key Rules

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

In short:

> Any attribute name that is valid in HTML may be used as a key.

Example:

```php
$form->email("email")
     ->setAttributes([
         'data-tracking' => 'enabled',
         'aria-required' => 'true'
     ]);
```

---

## 🧠 Value Handling Rules

| Value Type | Rendering Result |
|------------|------------------|
| `string` | Rendered as `attribute="value"` |
| `int` / `float` | Rendered as `attribute="number"` |
| `true` | Rendered as a boolean attribute (e.g. `required`) |
| `'true'` (string) | Rendered as `attribute="true"` |
| `false` / `null` | Attribute is not rendered |
| `array` (for `class` / `style`) | Values are merged automatically |

---

### Boolean Example

```php
$form->text("username")
     ->setAttributes([
         'required' => true
     ]);
```

Output:

```html
required
```

But:

```php
$form->text("username")
     ->setAttributes([
         'required' => 'true'
     ]);
```

Output:

```html
required="true"
```

---

## 🎯 When to Use `setAttributes()`

Use `setAttributes()` when:

- You need full control over the rendered attributes
- You want to add custom attributes
- You want to override fluent method values
- You are dynamically generating attribute sets
- You are working with `data-*` or `aria-*` attributes

---

## 🔐 Clean HTML Guarantee

When using `setAttributes()`:

- Duplicate attributes are not rendered multiple times (last value wins)
- Attribute keys are always rendered as HTML attributes (even custom ones)
- Empty string values are rendered as empty attributes (e.g. `attribute=""`)
- Boolean `true` renders attribute without value (e.g. `required`)
- Attribute precedence is respected (`setAttributes()` overrides fluent methods)
- HTML output remains valid and well-formed

In short:

> `setAttributes()` always renders the attributes you specify in a valid HTML form.

## 📥 Receiving & Validating Data

FormGenerator automatically extracts and validates submitted form data.

### Example

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

### Example Outputs

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
    'error' => "Validation error for key 'name': The value has length 2 and is below the allowed minimum.",
    'code'  => 422,
    'id'    => 'vE084',
    'file'  => '/var/www/html/index.php',
    'line'  => 42
]
```

> Each error always has a unique `id` and code for consistent handling. Only one error is returned at a time.

---

## 🛡️ Validation Rules (All Supported Rules)

| Rule | Aliases | Description |
|------|----------|-------------|
| Alpha | `a`, `al`, `alpha` | Letters only |
| AlphaNumber | `an`, `aln`, `alphan`, `alnum`, `alphanum`, `alphanumber` | Letters and numbers |
| Boolean | `b`, `bool`, `boolean` | Must be boolean |
| Color | `c`, `color` | Valid color value |
| Date | `d`, `date` | Valid date |
| DateTime | `dt`, `datetime` | Valid datetime |
| Email | `e`, `mail`, `email` | Valid email address |
| Enum | `in`, `en`, `enum` | Must be one of allowed values |
| Nenum | `nin`, `nen`, `nenum` | Must NOT be one of allowed values |
| Equal | `eq`, `equal` | Must equal given value |
| Nequal | `neq`, `nequal` | Must NOT equal given value |
| Extension | `ex`, `exten`, `extension` | File extension check |
| Mime | `mim`, `mime` | MIME type check |
| File | `f`, `file` | File input |
| Format | `fo`, `for`, `form`, `format` | Format validation |
| Ip | `i`, `ip` | Valid IP address |
| Json | `j`, `json` | Valid JSON |
| Length | `l`, `len`, `length` | Exact length |
| Max | `ma`, `max` | Maximum numeric value |
| MaxLength | `mal`, `malen`, `malength`, `maxl`, `maxlen`, `maxlength` | Maximum length |
| MaxWords | `maw`, `maword`, `mawords`, `maxw`, `maxword`, `maxwords` | Maximum word count |
| Min | `mi`, `min` | Minimum numeric value |
| MinLength | `mil`, `milen`, `milength`, `minl`, `minlen`, `minlength` | Minimum length |
| MinWords | `miw`, `miword`, `miwords`, `minw`, `minword`, `minwords` | Minimum word count |
| Month | `m`, `month` | Valid month |
| Number | `n`, `num`, `number` | Numeric value |
| Null | `null` | Must be null |
| NotNull | `nnull`, `notnull` | Must NOT be null |
| Password | `p`, `pas`, `pass`, `password` | Password validation |
| Regex | `re`, `reg`, `regex` | Regular expression match |
| Required | `r`, `req`, `required` | Field is required |
| Size | `si`, `size` | File size validation |
| StringRule | `s`, `str`, `string` | String validation |
| Tel | `te`, `tel` | Telephone number |
| Time | `ti`, `time` | Valid time |
| Url | `u`, `url` | Valid URL |
| Week | `w`, `week` | Valid week |

## 🔎 Standalone Validation

Validation engine can be used independently from form builder.

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

Supports **Google reCAPTCHA Enterprise** via:

- `google/cloud-recaptcha-enterprise`

### Setup

1. Create project in Google Cloud
2. Enable reCAPTCHA Enterprise
3. Generate site and secret keys
4. Initialize:

```php
Form::captchaInit(
    'project-id',
    'site-key',
    'secret-key'
);
```

Then add to form:

```php
$form->captcha();
```

---

## 🌱 Environment Variables (.env)

Optional support using:

- `vlucas/phpdotenv`

Example `.env`:

```
RECAPTCHA_PROJECT=
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET=
```

Library works without `.env` as well.

---

## 🧠 Validation Rules (Examples)

Rules can be written as:

- String: `'email;req'`
- Array: `['string', 'req', 'minl:c:3']`

### Common Rules

| Rule | Description |
|------|------------|
| string | Must be string |
| number | Numeric value |
| email | Valid email |
| bool | Boolean |
| req | Required |
| null | Allows null |
| min | Minimum numeric value |
| max | Maximum numeric value |
| minl | Minimum length |
| maxl | Maximum length |
| regex:/pattern/ | Custom regex |
| file | File validation |

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

## 🏛 Architecture

- Static entry point: `Form::new()`
- Also supports: `new Form(...)`
- Modular validation engine
- No framework dependency
- Pure PHP implementation

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
