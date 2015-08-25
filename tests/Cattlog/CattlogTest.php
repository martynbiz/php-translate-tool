<?php

use Cattlog\Cattlog;

class CattlogTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cattlog $cattlog Object we'll be testing
     */
     protected $cattlog;

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
        $this->adapterMock = $this->getMockBuilder('Cattlog\Adapters\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // mock file system
        $this->fsMock = $this->getMockBuilder('Cattlog\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cattlog = new Cattlog($this->adapterMock, $this->fsMock);
    }

    public function testGetInstanceOfClass()
    {
        $this->assertTrue($this->cattlog instanceof Cattlog);
    }

    public function testDiffKeys()
    {
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
        $added = $this->cattlog->getDiffAddedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'NEW_1',
            'NEW_2',
            'NEW_3',
        ), $added);

        // assert removed
        $added = $this->cattlog->getDiffRemovedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'REMOVED_1',
            'REMOVED_2',
        ), $added);
    }

    public function testAddKeys()
    {
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

        $this->cattlog->addKeys($actual, $keysToAdd);

        $this->assertEquals($expected, $actual);
    }

    public function testRemoveKeys()
    {
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

        $this->cattlog->removeKeys($actual, $keysToRemove);

        $this->assertEquals($expected, $actual);
    }

    public function testGetValue()
    {
        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $this->assertEquals($this->cattlog->getValue($data, 'TEST_1'), 'test 1');
        $this->assertEquals($this->cattlog->getValue($data, 'TEST_2'), 'test 2');
        $this->assertEquals($this->cattlog->getValue($data, 'TEST_XX'), null);
    }

    public function testSetValue()
    {
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

        $this->cattlog->setValue($actual, 'TEST_1', 'new 1');
        $this->cattlog->setValue($actual, 'TEST_2', 'new 2');
        $this->cattlog->setValue($actual, 'TEST_3', 'new 3');

        $this->assertEquals($expected, $actual);
    }

    public function testHasKey()
    {
        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
        );

        $this->assertTrue( $this->cattlog->hasKey($data, 'TEST_1') );
        $this->assertFalse( $this->cattlog->hasKey($data, 'TEST_10') );
        $this->assertFalse( $this->cattlog->hasKey($data, 'test 1') ); //
    }

    public function testSetValueWithCreateFalseOption()
    {
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

        $this->cattlog->setValue($actual, 'TEST_1', 'new 1', $options);
        $this->cattlog->setValue($actual, 'TEST_6', 'new 6', $options); // no set

        $this->assertEquals($expected, $actual);
    }

    public function testGetDestFile()
    {
        $this->cattlog->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $this->cattlog->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testGetDestFilesWhenDestIsString()
    {
        $this->cattlog->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $lang = 'en';

        // also checks that project_dir right slash is trimmed :)
        $expected = '/var/www/myproject/resources/lang/en/messages.php';

        $actual = $this->cattlog->getDestFile($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testGetDataTest()
    {
        $this->cattlog->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $expected = array(
            'test_1' => 'hello world!',
        );

        $lang = 'en';
        $file = $this->cattlog->getDestFile($lang);

        $this->adapterMock
            ->expects($this->once())
            ->method('getData')
            ->with($file)
            ->willReturn($expected);

        $actual = $this->cattlog->getData($lang);

        $this->assertEquals($expected, $actual);
    }

    public function testPutDataTest()
    {
        $this->cattlog->setConfig(array(
            'dest' => 'resources/lang/{lang}/messages.php',
            'project_dir' => '/var/www/myproject/',
        ));

        $data = array(
            'test_1' => 'hello world!',
        );

        $lang = 'en';
        $file = $this->cattlog->getDestFile($lang);

        $this->adapterMock
            ->expects($this->once())
            ->method('putData')
            ->with($file, $data);

        $actual = $this->cattlog->putData('en', $data);
    }
}
