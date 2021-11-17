# Captcha for Hyperf

This is the Captcha component for Hyperf 2.

## Installation

```
$ composer require kilofox/hyperf-captcha
```

## Publish

```
$ php bin/hyperf.php vendor:publish kilofox/hyperf-captcha
```

## Usage

Instantiate captcha factory:

```
use Hyperf\Utils\ApplicationContext;
use Kilofox\Captcha\CaptchaFactory;

$captchaFactory = ApplicationContext::getContainer()->get(CaptchaFactory::class);
```

Render a captcha:

```
$group = $this->request->query('group', 'default');
$key = $this->request->query('key');
$captcha = $captchaFactory->create($group, $key);

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
