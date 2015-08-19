<?php

use Cattlog\FileSystem;

abstract class BaseAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cattlog $cattlog Object we'll be testing
     */
     protected $adapter;

    /**
     * @var FileSystem_mock $fs
     */
     protected $fsMock;

    public function setUp()
    {
        // mock file system
        $this->fsMock = $this->getMockBuilder('Cattlog\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSetConfig()
    {
        $expected = array(
            'pattern' => '/new_pattern/',
            'languages' => array(
                'en',
                'ja',
                'ru',
            ),
        );

        $this->adapter->setConfig($expected);

        $actual = $this->adapter->getConfig();

        $this->assertEquals($expected['pattern'], $actual['pattern']);
        $this->assertEquals($expected['languages'], $actual['languages']);
    }
}
