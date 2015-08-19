<?php

use Cattlog\Adapters\CattlogLaravel5Adapter;
use Cattlog\FileSystem;

// require 'BaseAdapterTest.php';

class CattlogLaravel5AdapterTest extends BaseAdapterTest
{
    public function setUp()
    {
       parent::setUp();

       // instantiate the cattlog obj
       $this->adapter = new CattlogLaravel5Adapter($this->fsMock);
    }

    public function testGetInstanceOfClass()
    {
        $this->assertTrue($this->adapter instanceof CattlogLaravel5Adapter);
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

    public function testRemove()
    {
        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => array(
                'NEST_1' => 'Nest 1',
                'NEST_2' => array(
                    'DEEP_1' => 'Deep 1',
                    'DEEP_2' => 'Deep 2',
                )
            ),
            'TEST_6' => array(
                'NEST_6' => array(), // an empty to be cleaned
            ),

            // test empties are removed
            'TEST_11' => 'test 1',
            'TEST_12' => array(),
            'TEST_13' => 'test 3',
            'TEST_14' => array(
                'NESTED_1' => array(),
                'NESTED_2' => array(
                    'DEEP_1' => array(),
                ),
            ),
            'TEST_15' => array(
                'NESTED_1' => array(),
                'NESTED_2' => array(
                    'DEEP_1' => 'test nested deep 1',
                ),
            ),
        );

        $keysToRemove = array(
            'TEST_2',
            'TEST_4',
            'TEST_5.NEST_2.DEEP_2',
        );

        $expected = array(
            'TEST_1' => 'test 1',
            'TEST_3' => 'test 3',
            'TEST_5' => array(
                'NEST_1' => 'Nest 1',
                'NEST_2' => array(
                    'DEEP_1' => 'Deep 1',
                )
            ),

            // empties should have been removed from here...
            'TEST_11' => 'test 1',
            'TEST_13' => 'test 3',
            'TEST_15' => array(
                'NESTED_2' => array(
                    'DEEP_1' => 'test nested deep 1',
                ),
            ),
        );

        $actual = $this->adapter->remove($data, $keysToRemove);

        $this->assertEquals($expected, $actual);
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

        $keysToAdd = array('TEST_2', 'TEST_6', 'TEST_7', 'TEST_8.NEST_1');

        $expected = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2', // this one shouldn't be blank
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => 'test 5',
            'TEST_6' => '',
            'TEST_7' => '',
            'TEST_8' => array(
                'NEST_1' => '',
            )
        );

        $actual = $this->adapter->add($data, $keysToAdd);

        $this->assertEquals($expected, $actual);
    }

    public function testGetValue()
    {
        $data = array(
            'TEST_1' => 'test 1',
            'TEST_2' => 'test 2',
            'TEST_3' => 'test 3',
            'TEST_4' => 'test 4',
            'TEST_5' => array(
                'NEST_1' => 'nest 1'
            ),
        );

        ;
        $this->adapter->getValue($data, 'TEST_2');
        $this->adapter->getValue($data, 'TEST_XX');

        $this->assertEquals($this->adapter->getValue($data, 'TEST_1'), 'test 1');
        $this->assertEquals($this->adapter->getValue($data, 'TEST_5.NEST_1'), 'nest 1');
        $this->assertEquals($this->adapter->getValue($data, 'TEST_XX'), null);
    }

    public function testSetValue()
    {
        $actual = array(
            'errors' => array(
                'email' => 'Email',
                'req' => array(
                    'nested' => 'Nested'
                ),
            ),
        );

        // we'll create a copy from actual first, setValue will alter it
        $expected = array(
            'errors' => array(
                'email' => 'Email SET',
                'req' => array(
                    'nested' => 'Nested SET'
                ),
            ),
            'shunsuke' => 'GOAL',
        );

        $this->adapter->setValue($actual, 'errors.email', 'Email SET');
        $this->adapter->setValue($actual, 'errors.req.nested', 'Nested SET');
        $this->adapter->setValue($actual, 'shunsuke', 'GOAL');

        $this->assertEquals($expected, $actual);
    }

    public function testSetValueWithCreateFalseOption()
    {
        $actual = array(
            'errors' => array(
                'email' => 'Email',
                'req' => array(
                    'nested' => 'Nested'
                ),
            ),
        );

        // we'll create a copy from actual first, setValue will alter it
        $expected = array(
            'errors' => array(
                'email' => 'Email SET',
                'req' => array(
                    'nested' => 'Nested SET'
                ),
            ),
            // 'shunsuke' => 'GOAL', // not set
        );

        $options = array(
            'create' => false,
        );

        $this->adapter->setValue($actual, 'errors.email', 'Email SET', $options);
        $this->adapter->setValue($actual, 'errors.req.nested', 'Nested SET', $options);
        $this->adapter->setValue($actual, 'shunsuke', 'GOAL', $options);

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
    //     $this->adapter->setValue($actual, 'errors.email', 'Email SET', $options);
    //     $this->adapter->setValue($actual, 'errors.req.nested', 'Nested SET', $options);
    //     $this->adapter->setValue($actual, 'shunsuke', 'GOAL', $options);
    //
    //     $this->assertEquals($expected, $actual);
    // }

    public function testHasKey()
    {
        $data = array(
            'errors' => array(
                'email' => 'Email',
                'req' => array(
                    'nested' => 'Nested',
                ),
            ),
        );

        $this->assertTrue( $this->adapter->hasKey($data, 'errors.email') );
        $this->assertTrue( $this->adapter->hasKey($data, 'errors.req.nested') );
        $this->assertFalse( $this->adapter->hasKey($data, 'shunsuke') );
        $this->assertFalse( $this->adapter->hasKey($data, 'shunsuke.nested') );
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
            'messages.hello' => 'Hello world!',
        );

        $actual = $this->adapter->getKeysWithValuesFromDestFiles('en');

        $this->assertEquals($expected, $actual);

        // test getKeysFromDestFiles too since we've setup our mock

        $actualKeys = $this->adapter->getKeysFromDestFiles('en');

        $this->assertEquals(array_keys($expected), $actualKeys);
    }

    public function testGetKeysFromSrcFiles()
    {
        $this->fsMock
            ->method('getSrcFiles')
            ->willReturn( array(
                '/path/to/views/home/index.phtml',
            ) );

        $this->fsMock
            ->method('getFileContents')
            ->willReturn( '<h1>{{trans(\'headers.trans.single_quotes\')}}</h1>' . PHP_EOL
            . '<p>{{Lang::get(\'para.lang.single_quotes\')}}</p>' . PHP_EOL
            . '<p>{{trans("para.trans.double_quotes")}}</p>' . PHP_EOL
            . '<p>{{Lang::get("para.lang.double_quotes")}}</p>' . PHP_EOL
            . '<p>{{trans ( "para.trans.whitespace")}}</p>' . PHP_EOL);

        $expected = array(
            'headers.trans.single_quotes',
            'para.lang.single_quotes',
            'para.trans.double_quotes',
            'para.lang.double_quotes',
            'para.trans.whitespace',
        );

        $actual = $this->adapter->getKeysFromSrcFiles();

        // order is only important for assertion
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }
}
