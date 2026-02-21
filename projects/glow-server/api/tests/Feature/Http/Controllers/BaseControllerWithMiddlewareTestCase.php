<?php

namespace Tests\Feature\Http\Controllers;

abstract class BaseControllerWithMiddlewareTestCase extends BaseControllerTestCase
{
    public function disableMiddlewareForAllTests()
    {
        // WithoutMiddlewareトレイトを無効化する
    }
}
