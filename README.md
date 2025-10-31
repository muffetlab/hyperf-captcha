# Captcha for Hyperf

This is the Captcha component for Hyperf 2.

[![Release Version](https://img.shields.io/github/release/muffetlab/hyperf-captcha.svg)](https://github.com/muffetlab/hyperf-captcha/releases/latest) [![Latest Release Download](https://img.shields.io/github/downloads/muffetlab/hyperf-captcha/latest/total.svg)](https://github.com/muffetlab/hyperf-captcha/releases/latest) [![Total Download](https://img.shields.io/github/downloads/muffetlab/hyperf-captcha/total.svg)](https://github.com/muffetlab/hyperf-captcha/releases)

## Installation

```
$ composer require muffetlab/hyperf-captcha
```

## Publish

```
$ php bin/hyperf.php vendor:publish muffetlab/hyperf-captcha
```

## Usage

Instantiate captcha factory:

```
use Hyperf\Utils\ApplicationContext;
use Muffetlab\Captcha\CaptchaFactory;

$captchaFactory = ApplicationContext::getContainer()->get(CaptchaFactory::class);
```

> **Note:** Since version 1.1.0, the package namespace has been changed from `Kilofox` to `Muffetlab`. If you're
> upgrading from a previous version, please update your code accordingly:

```
// Old namespace (before 1.1.0)
use Kilofox\Captcha\CaptchaFactory;

// New namespace (since 1.1.0)q
use Muffetlab\Captcha\CaptchaFactory;
```

Render a captcha:

```
$key = $this->request->query('key');
$captcha = $captchaFactory->create($key);

return $captcha->render();
```

Validate the captcha:

```
$key = $this->request->input('key', '');
$captcha = $this->request->input('captcha', '');

$captchaFactory->validate($key, $captcha);
```

## Supported Captcha Styles

* alpha
* basic
* black
* math
* riddle
* word
