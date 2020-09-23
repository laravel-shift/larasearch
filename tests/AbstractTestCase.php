<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
