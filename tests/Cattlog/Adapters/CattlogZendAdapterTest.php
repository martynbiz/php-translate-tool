<?php

use Cattlog\Adapters\CattlogZendAdapter;
use Cattlog\FileSystem;

// require 'BaseAdapterTest.php';

class CattlogZendAdapterTest extends BaseAdapterTest
{
    public function setUp()
    {
       parent::setUp();

       // instantiate the cattlog obj
       $this->adapter = new CattlogZendAdapter($this->fsMock);
    }

    public function testGetInstanceOfClass()
    {
        $this->assertTrue($this->adapter instanceof CattlogZendAdapter);
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
        $added = $this->adapter->getDiffAddedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'NEW_1',
            'NEW_2',
            'NEW_3',
        ), $added);

        // assert removed
        $added = $this->adapter->getDiffRemovedKeys($old, $new);
        sort($added);
        $this->assertEquals(array(
            'REMOVED_1',
            'REMOVED_2',
        ), $added);
    }

    public function testAdd()
    {
        $data = array(
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
            'TEST_6' => '',
            'TEST_7' => '',
        );

        $actual = $this->adapter->add($data, $keysToAdd);

        $this->assertEquals($expected, $actual);
    }

    public function testRemove()
    {
        $data = array(
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
        );;

        $actual = $this->adapter->remove($data, $keysToRemove);

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

        $this->assertEquals($this->adapter->getValue($data, 'TEST_1'), 'test 1');
        $this->assertEquals($this->adapter->getValue($data, 'TEST_2'), 'test 2');
        $this->assertEquals($this->adapter->getValue($data, 'TEST_XX'), null);
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

        $this->adapter->setValue($actual, 'TEST_1', 'new 1');
        $this->adapter->setValue($actual, 'TEST_2', 'new 2');
        $this->adapter->setValue($actual, 'TEST_3', 'new 3');

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

        $this->assertTrue( $this->adapter->hasKey($data, 'TEST_1') );
        $this->assertFalse( $this->adapter->hasKey($data, 'TEST_10') );
        $this->assertFalse( $this->adapter->hasKey($data, 'test 1') ); //
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

        $this->adapter->setValue($actual, 'TEST_1', 'new 1', $options);
        $this->adapter->setValue($actual, 'TEST_6', 'new 6', $options); // no set

        $this->assertEquals($expected, $actual);
    }

    public function testGetKeysWithValuesFromDestFiles()
    {
        $this->fsMock
            ->method('getDestFiles')
            ->willReturn( array(
                '/path/to/lang/en/messages.php',
            ) );

        $this->fsMock
            ->method('getFileData')
            ->willReturn( array(
                'hello' => 'Hello world!',
            ) );

        $this->fsMock
            ->method('fileExists')
            ->willReturn( true );

        $expected = array(
            'hello' => 'Hello world!',
        );

        $actual = $this->adapter->getKeysWithValuesFromDestFiles('en');

        $this->assertEquals($expected, $actual);

        // test getKeysFromDestFiles too since we've setup our mock

        $actualKeys = $this->adapter->getKeysFromDestFiles('en');

        $this->assertEquals(array_keys($expected), $actualKeys);
    }
}
