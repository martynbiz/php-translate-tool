<?php

use MartynBiz\Translate\Tool\FileSystem;

class FileSystemTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testGetInstanceOfClass()
    {
        $fileSystem = new FileSystem();

        $this->assertTrue($fileSystem instanceof FileSystem);
    }
}
