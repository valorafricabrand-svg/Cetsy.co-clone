<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (function_exists('forget_settings_runtime_cache')) {
            forget_settings_runtime_cache();
        }

        if (empty(config('app.key'))) {
            config([
                'app.key' => 'base64:' . base64_encode(random_bytes(32)),
            ]);
        }
    }
}
