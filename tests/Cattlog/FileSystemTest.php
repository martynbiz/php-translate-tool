<?php

use Cattlog\FileSystem;

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

    public function testGetDestFile()
    {
        $fileSystem = new FileSystem(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $fileSystem->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testGetDestFilesWhenDestIsString()
    {
        $fileSystem = new FileSystem(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $fileSystem->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }
}
