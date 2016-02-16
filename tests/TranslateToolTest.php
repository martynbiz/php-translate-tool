<?php

use MartynBiz\Translate\Tool\TranslateTool;

class TranslateToolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter_mock $fs
     */
     protected $adapterMock;

    /**
     * @var FileSystem_mock $fs
     */
     protected $fsMock;

    public function setUp()
    {
        // mock adapter
        $this->adapterMock = $this->getMockBuilder('MartynBiz\\Translate\\Tool\\Adapter\\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // mock file system
        $this->fsMock = $this->getMockBuilder('MartynBiz\\Translate\\Tool\\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetInstanceOfClass()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $this->assertTrue($tool instanceof TranslateTool);
    }

    public function testDiffKeys()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        // test data
        $old = array(
            'REMOVED_2',
            'REMAIN_1',
            'REMAIN_2',
            'REMOVED_1',
            'REMAIN_3',
        );

        // newly scanned keys
        $new = array(
            'REMAIN_3',
            'REMAIN_2',
            'NEW_1',
            'REMAIN_1',
            'NEW_2',
            'NEW_3',
        );

        // assert added
        $added = $tool->getDiffAddedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'NEW_1',
            'NEW_2',
            'NEW_3',
        ), $added);

        // assert removed
        $added = $tool->getDiffRemovedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'REMOVED_1',
            'REMOVED_2',
        ), $added);
    }

    public function testAddKeys()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $actual = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $keysToAdd = array('TEST_2', 'TEST_6', 'TEST_7');

        $expected = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2', // this one shouldn't be blank
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
            'TEST_6' => 'TEST_6',
            'TEST_7' => 'TEST_7',
        );

        $tool->addKeys($actual, $keysToAdd);

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveKeys()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $actual = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
            'TEST_6' => 'test 6',
        );

        $keysToRemove = array(
            'TEST_2',
            'TEST_4',
            'TEST_5',
        );

        $expected = array(
            'TEST_1' => 'test 1',
            'TEST_3' => 'test 3',
            'TEST_6' => 'test 6',
        );

        $tool->removeKeys($actual, $keysToRemove);

        $this->assertEquals($expected, $actual);
    }

    public function testGetValue()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $this->assertEquals($tool->getValue($data, 'TEST_1'), 'test 1');
        $this->assertEquals($tool->getValue($data, 'TEST_2'), 'test 2');
        $this->assertEquals($tool->getValue($data, 'TEST_XX'), null);
    }

    public function testSetValue()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $actual = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        // we'll create a copy from actual first, setValue will alter it
        $expected = array(
            'TEST_1' => 'new 1',
            'TEST_2' => 'new 2',
            'TEST_3' => 'new 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $tool->setValue($actual, 'TEST_1', 'new 1');
        $tool->setValue($actual, 'TEST_2', 'new 2');
        $tool->setValue($actual, 'TEST_3', 'new 3');

        $this->assertEquals($expected, $actual);
    }

    public function testHasKey()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $this->assertTrue( $tool->hasKey($data, 'TEST_1') );
        $this->assertFalse( $tool->hasKey($data, 'TEST_10') );
        $this->assertFalse( $tool->hasKey($data, 'test 1') ); //
    }

    public function testSetValueWithCreateFalseOption()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $actual = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        // we'll create a copy from actual first, setValue will alter it
        $expected = array(
            'TEST_1' => 'new 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $options = array(
            'create' => false,
        );

        $tool->setValue($actual, 'TEST_1', 'new 1', $options);
        $tool->setValue($actual, 'TEST_6', 'new 6', $options); // no set

        $this->assertEquals($expected, $actual);
    }

    public function testGetDestFile()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $tool->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $tool->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testGetDestFilesWhenDestIsString()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $tool->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $tool->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testGetDataTest()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $tool->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $expected = array(
            'test_1' => 'hello world!',
        );

        $lang = 'en';
        $file = $tool->getDestFile($lang);

        $this->adapterMock
            ->expects( $this->once() )
            ->method('getData')
            ->with($file)
            ->willReturn($expected);

        $actual = $tool->getData($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testPutDataTest()
    {
        $tool = new TranslateTool($this->adapterMock, $this->fsMock);

        $tool->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $data = array(
            'test_1' => 'hello world!',
        );

        $lang = 'en';
        $file = $tool->getDestFile($lang);

        $this->adapterMock
            ->expects($this->once())
            ->method('putData')
            ->with($file, $data);

        $actual = $tool->putData('en', $data);
    }
}
