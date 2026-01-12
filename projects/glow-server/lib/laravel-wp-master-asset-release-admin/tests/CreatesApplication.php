<?php

namespace WonderPlanet\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // TODO ライブラリ側からadmin側のbootstrap/app.phpを直接指定しないようにしたい
        $app = require '/var/www/tests/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
