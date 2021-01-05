<?php
/**
 * DocumentStore
 * Copyright 2020-2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace DocumentStore\Test;

use DocumentStore\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testSet()
    {
        $document = new Document();

        $document->foo = 'bar';
        $this->assertEquals('bar', $document->foo);

        $document->foo = null;
        $this->assertNull($document->foo);

        $document->set('bar', 'foo');
        $this->assertEquals('foo', $document->bar);

        $document->set(['a' => 'b']);
        $this->assertEquals('b', $document->a);

        $document->set('bar', null);
        $this->assertNull($document->bar);

        $document['key'] = 'value';
        $this->assertEquals('value', $document->key);
    }

    public function testGet()
    {
        $document = new Document();
        $document->foo = 'bar';

        $this->assertEquals('bar', $document->foo);
        $this->assertEquals('bar', $document->get('foo'));
        $this->assertEquals('bar', $document['foo']);

        // non existant
        $this->assertNull($document->bar);
        $this->assertNull($document->get('bar'));
        $this->assertNull($document['bar']);

        $document->foo = null;
        $this->assertNull($document->foo);
        $this->assertNull($document->get('foo'));
        $this->assertNull($document['foo']);
    }

    public function testHas()
    {
        $document = new Document();
        $document->foo = 'bar';

        $this->assertTrue($document->has('foo'));
        $this->assertTrue(isset($document->foo));
        $this->assertTrue(isset($document['foo']));

        $this->assertFalse($document->has('bar'));
        $this->assertFalse(isset($document->bar));
        $this->assertFalse(isset($document['bar']));
    }

    public function testUnset()
    {
        $document = new Document();
        $document->foo = 'bar';
        $document->bar = 'foo';
        $document->foobar = 'foobar';

        $this->assertTrue($document->unset('foo'));

        unset($document->bar);
        $this->assertFalse($document->has('bar'));

        unset($document['foobar']);
        $this->assertFalse($document->has('foobar'));

        // test non existant
        unset($document->nonExistant1);
        unset($document['nonExistant2']);
        $this->assertFalse($document->unset('nonExistant3'));
    }

    public function testToString()
    {
        $document = new Document();
        $document->name = 'foo';
        $document->description = 'bar';
        $expected = "{\n    \"name\": \"foo\",\n    \"description\": \"bar\"\n}";

        $this->assertEquals($expected, (string) $document);
    }

    public function testSetState()
    {
        $document = Document::__set_state(['foo' => 'bar']);
        $this->assertEquals('bar', $document->foo);
    }

    public function testJsonSerialize()
    {
        $data = ['foo' => 'bar'];
        $document = new Document($data);
        $this->assertEquals($data, $document->jsonSerialize());
    }

    public function testDebugInfo()
    {
        $data = ['foo' => 'bar'];
        $document = new Document($data);
        $this->assertEquals($data, $document->__debugInfo());
    }

    public function testSerialize()
    {
        $document = new Document(['foo' => 'bar']);
        $this->assertEquals('a:1:{s:3:"foo";s:3:"bar";}', $document->serialize());
    }
    
    public function testUnserialize()
    {
        //['foo' => 'bar']
        $document = new Document();
        $document->unserialize('a:1:{s:3:"foo";s:3:"bar";}');
        
        $this->assertEquals(['foo' => 'bar'], $document->toArray());
    }
}
