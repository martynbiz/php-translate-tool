<?php

use Cattlog\Adapters\Php;
use Cattlog\FileSystem;

// require 'BaseAdapterTest.php';

class PhpTest extends BaseAdapterTest
{
    public function setUp()
    {
       parent::setUp();

       // instantiate the cattlog obj
       $this->adapter = new Php($this->fsMock);
    }

    public function testGetInstanceOfClass()
    {
        $this->assertTrue($this->adapter instanceof Php);
    }
}
