<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private $data = [
        'string' => 'value',
        'array' => [1, 2],
        'object' => [
            'sub_object' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ],
    ];

    /**
     * Test the constructor.
     */
    public function testConstructor()
    {
        $config = new Config($this->data);
        $this->assertSame($this->data, $config->toArray());
    }

    /**
     * Test the "set", "get" and "has" methods.
     */
    public function testSetValue()
    {
        $config = new Config();
        $config->set('key', 'value');

        $this->assertTrue($config->has('key'));
        $this->assertSame('value', $config->get('key'));
        $this->assertSame(['key' => 'value'], $config->toArray());
    }

    /**
     * Test the "merge" method.
     */
    public function testMerge()
    {
        $config = new Config();
        $config->merge($this->data);

        // Assert that numeric arrays are replaced
        $newArray = [2, 3];
        $config->merge(['array' => $newArray]);
        $this->assertSame($newArray, $config->get('array'));

        // Assert that associative arrays are properly merged
        $newObject = ['sub_object' => ['key2' => 'new_value', 'key3' => 'value3']];
        $expectedObject = ['sub_object' => array_merge($this->data['object']['sub_object'], $newObject['sub_object'])];
        $config->merge(['object' => $newObject]);
        $this->assertSame($expectedObject, $config->get('object'));

        // Assert that objects set to null are removed from the config array if they are already defined
        $config->merge(['object' => ['sub_object' => null]]);
        $this->assertFalse($config->has('object'));

        // Assert that objects set to null are added to the array if they are not already
        $config->merge(['object2' => ['sub_object' => null]]);
        $this->assertSame(['sub_object' => null], $config->get('object2'));
    }

    /**
     * Test the behavior of the "get" method when the specified key is not defined.
     */
    public function testValueNotFound()
    {
        $config = new Config($this->data);

        $this->assertFalse($config->has('not_exists'));
        $this->assertSame('defaultValue', $config->get('not_exists', 'defaultValue'));
    }
}
