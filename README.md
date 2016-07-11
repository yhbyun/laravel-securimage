# Laravel 5 Securimage helper

[![Build Status](https://travis-ci.org/yhbyun/laravel-securimage.svg?branch=master)](https://travis-ci.org/yhbyun/laravel-securimage)

A simple laravel 5 wrapper package for [Securimage](https://www.phpcaptcha.org/).

![preview](https://raw.githubusercontent.com/yhbyun/resources/master/securimage/securimage.png)

## Installation

Add the package to your `composer.json` by running:

```
composer require yhbyun/laravel-securimage
```

When it's installed, add it to the providers list in `config/app.php`

```
Yhbyun\Securimage\SecurimageServiceProvider::class,
```

Publish assets to your public folder

```
$ php artisan vendor:publish --provider="Yhbyun\Securimage\SecurimageServiceProvider"
```

## Configuration

`config/securimage.php`

```php
return [
    'length' => env('SECURIMAGE_LENGTH', 6),
    'width'  => env('SECURIMAGE_WIDTH', 215),
    'height'  => env('SECURIMAGE_HEIGHT', 80),
    'perturbation' => env('SECURIMAGE_PERTURBATION', .85),
    'case_sensitive' => env('SECURIMAGE_CASE_SENSITIVE', false),
    'num_lines' => env('SECURIMAGE_NUM_LINES', 0),
    'charset' => env('SECURIMAGE_CHARSET', 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789'),
    'signature' => env('SECURIMAGE_SIGNATURE', 'ecplaza'),
    'show_text_input' => env('SECURIMAGE_SHOW_TEXT_INPUT', false),
];
```

## Example Usage

`app/Http/routes.php`

```
Route::any('captcha-test', function()
{
    if (Request::getMethod() == 'POST')
    {
        $rules = ['captcha' => 'required|captcha'];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails())
        {
            echo '<p style="color: #ff0000;">Incorrect!</p>';
        }
        else
        {
            echo '<p style="color: #00ff30;">Matched :)</p>';
        }
    }

    $form = '<form method="post" action="captcha-test">';
    $form .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    $form .= '<p>' . captcha_img() . '</p>';
    $form .= '<p><button type="submit" name="check">Check</button></p>';
    $form .= '</form>';
    return $form;
});
```


## Testing

```
$ phpunit --stderr
```
