<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }
}
