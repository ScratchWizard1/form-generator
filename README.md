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

### Requirements

- PHP >= 8.0 (tested on 8.3 and 8.4)
- ext-json

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

### Validation Rules Used

| Rule | Description |
|------|------------|
| string | Must be a string |
| email | Must be a valid email address |
| required | Field must be present and not empty |
| minlength | Minimum number of characters |

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
