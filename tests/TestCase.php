<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use function Prinx\Dotenv\loadEnv;

/**
 * Base Test case.
 */
abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        $this->loadEnv(realpath(__DIR__.'/../').'/.env');
    }

    public function createEnvIfNotExist($path)
    {
        if (!file_exists($path)) {
            file_put_contents($path, '');
        }
    }

    public function loadEnv($env)
    {
        $this->createEnvIfNotExist($env);

        loadEnv($env);
    }
}
