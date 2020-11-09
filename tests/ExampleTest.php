<?php

namespace Sabinks\FreshbooksClientPhp\Tests;

use Orchestra\Testbench\TestCase;
use Sabinks\FreshbooksClientPhp\FreshbooksClientPhpServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [FreshbooksClientPhpServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
