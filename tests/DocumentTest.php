<?php

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
}
