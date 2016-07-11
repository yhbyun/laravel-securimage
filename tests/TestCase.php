<?php

namespace Yhbyun\Securimage\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Yhbyun\Securimage\SecurimageServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('securimage.securimage_path', '/vendor/securimage');
    }

    protected function getPackageProviders($app)
    {
        return [
            SecurimageServiceProvider::class,
        ];
    }
}
