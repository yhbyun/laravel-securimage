<?php

namespace Yhbyun\Securimage\Test;

use Yhbyun\Securimage\SecurimageServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('securimage.securimage_path', '/vendor/securimage');
    }

    protected function getPackageProviders($app)
    {
        return [
            SecurimageServiceProvider::class
        ];
    }
}
