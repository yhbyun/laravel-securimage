<?php

namespace Yhbyun\Securimage;

use Illuminate\Support\ServiceProvider;

class SecurimageServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $app = $this->app;

        $path = __DIR__ . '/../config/securimage.php';
        $this->publishes([$path => config_path('securimage.php')], 'config');

        $path = __DIR__ . '/../../../dapphp/securimage/audio';
        $this->publishes([$path => public_path('vendor/securimage/audio')], 'audio');

        $path = __DIR__ . '/../../../dapphp/securimage/images';
        $this->publishes([$path => public_path('vendor/securimage/images')], 'images');

        $path = __DIR__ . '/../../../dapphp/securimage/backgrounds';
        $this->publishes([$path => public_path('vendor/securimage/backgrounds')], 'backgrounds');

        $path = __DIR__ . '/../../../dapphp/securimage/securimage.js';
        $this->publishes([$path => public_path('vendor/securimage/securimage.js')], 'javascript');

        $path = __DIR__ . '/../../../dapphp/securimage/securimage.css';
        $this->publishes([$path => public_path('vendor/securimage/securimage.css')], 'css');

        $app['validator']->extend('captcha', function ($field, $value, $param) use ($app) {
            return $app['securimage']->validator($value);
        }, 'Captcha is invalid!');

        $app['router']->get('securimage', array('as' => 'securimage', 'uses' => 'Yhbyun\Securimage\SecurimageController@getCaptcha'));

        $app['router']->get('securimage/audio', array('as' => 'securimage.audio', 'uses' => 'Yhbyun\Securimage\SecurimageController@getAudio'));

        $app['router']->get('securimage/check', array('as' => 'securimage.check', 'uses' => 'Yhbyun\Securimage\SecurimageController@check'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $path = __DIR__ . '/../config/securimage.php';
        $this->mergeConfigFrom($path, 'securimage');

        $this->app['securimage'] = $this->app->share(function ($app) {
            return new Securimage($this->app['config']);
        });
    }
}
