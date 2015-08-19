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

    public function testGetDestFiles()
    {
        $fileSystem = new FileSystem(array(
            'dest' => array(
                'resources/lang/{lang}/messages.php',
                'resources/lang/{lang}/errors.php',
            ),
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = array(
            '/var/www/myproject/resources/lang/en/messages.php',
            '/var/www/myproject/resources/lang/en/errors.php',
        );

        $actual = $fileSystem->getDestFiles($lang);

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
        $expected = array(
            '/var/www/myproject/resources/lang/en/messages.php',
        );

        $actual = $fileSystem->getDestFiles($lang);

        $this->assertEquals($expected, $actual);
    }

    // public function testGetDestFileByCollection()
    // {
    //     $fileSystem = new FileSystem(array(
    //         'dest' => array(
    //             'resources/lang/{lang}/messages.php',
    //             'resources/lang/{lang}/errors.php',
    //         ),
    //         'project_dir' => '/var/www/myproject/',
    //     ));
    //
    //     // existing collection
    //
    //     $lang = 'en';
    //     $collection = 'messages';
    //
    //     $expected = '/var/www/myproject/resources/lang/en/messages.php';
    //
    //     $actual = $fileSystem->getDestFileByCollection($lang, $collection);
    //
    //     $this->assertEquals($expected, $actual);
    //
    //     // missing collection
    //
    //     $lang = 'en';
    //     $collection = 'missing';
    //
    //     $expected = null;
    //
    //     $actual = $fileSystem->getDestFileByCollection($lang, $collection);
    //
    //     $this->assertEquals($expected, $actual);
    // }

    public function testGetDestArrayByKey()
    {
        $fileSystem = new FileSystem(array(
            'dest' => array(
                'resources/lang/{lang}/messages.php',
                'resources/lang/{lang}/errors.php',
            ),
            'project_dir' => '/var/www/myproject/',
        ));

        // existing collection

        $lang = 'en';
        $key = 'messages.header.title';

        $expected = array(
            '/var/www/myproject/resources/lang/en/messages.php',
            'header.title',
        );

        $actual = $fileSystem->getDestArrayByKey($lang, $key);

        $this->assertEquals($expected, $actual);

        // missing collection

        $lang = 'en';
        $key = 'missing.header.title';

        $expected = array(
            null,
            'header.title',
        );

        $actual = $fileSystem->getDestArrayByKey($lang, $key);

        $this->assertEquals($expected, $actual);
    }

    // public function testSetValueWithOverwriteFalseOption()
    // {
    //     $actual = array(
    //         'errors' => array(
    //             'email' => 'Email',
    //             'req' => array(
    //                 'nested' => 'Nested'
    //             ),
    //         ),
    //     );
    //
    //     // we'll create a copy from actual first, setValue will alter it
    //     $expected = array(
    //         'errors' => array(
    //             'email' => 'Email',
    //             'req' => array(
    //                 'nested' => 'Nested'
    //             ),
    //         ),
    //         'shunsuke' => 'GOAL',
    //     );
    //
    //     $options = array(
    //         'overwrite' => false,
    //     );
    //
    //     $this->cattlog->setValue($actual, 'errors.email', 'Email SET', $options);
    //     $this->cattlog->setValue($actual, 'errors.req.nested', 'Nested SET', $options);
    //     $this->cattlog->setValue($actual, 'shunsuke', 'GOAL', $options);
    //
    //     $this->assertEquals($expected, $actual);
    // }
    //
    // public function testHasValue()
    // {
    //     $data = array(
    //         'errors' => array(
    //             'email' => 'Email',
    //             'req' => array(
    //                 'nested' => 'Nested',
    //             ),
    //         ),
    //     );
    //
    //     $this->assertTrue( $this->cattlog->hasValue($data, 'errors.email') );
    //     $this->assertTrue( $this->cattlog->hasValue($data, 'errors.req.nested') );
    //     $this->assertFalse( $this->cattlog->hasValue($data, 'shunsuke') );
    //     $this->assertFalse( $this->cattlog->hasValue($data, 'shunsuke.nested') );
    // }
}
