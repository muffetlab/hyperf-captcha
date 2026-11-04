# Captcha for Hyperf

This is the Captcha component for Hyperf 3.

[![Release Version](https://img.shields.io/github/release/muffetlab/hyperf-captcha.svg)](https://github.com/muffetlab/hyperf-captcha/releases/latest) [![Latest Release Download](https://img.shields.io/github/downloads/muffetlab/hyperf-captcha/latest/total.svg)](https://github.com/muffetlab/hyperf-captcha/releases/latest) [![Total Download](https://img.shields.io/github/downloads/muffetlab/hyperf-captcha/total.svg)](https://github.com/muffetlab/hyperf-captcha/releases)

## Installation

```
$ composer require muffetlab/hyperf-captcha
```

## Publish

```
$ php bin/hyperf.php vendor:publish muffetlab/hyperf-captcha
```

## Upgrading

This version targets Hyperf 3 and contains breaking changes:

* PHP >= 8.0 and Hyperf ^3.0 are required.
* The `fontpath` config option has been renamed to `font_path`. If you have previously published the config,
  update `config/autoload/captcha.php` accordingly, or re-publish it with the command above.
* `Hyperf\Utils\ApplicationContext` has been deprecated by Hyperf; use `Hyperf\Context\ApplicationContext` instead.

## Usage

Instantiate captcha factory:

```
use Hyperf\Context\ApplicationContext;
use Muffetlab\Captcha\CaptchaFactory;

$captchaFactory = ApplicationContext::getContainer()->get(CaptchaFactory::class);
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
