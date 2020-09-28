<?php

use DocumentStore\Document;
use PHPUnit\Framework\TestCase;
use DocumentStore\DocumentStore;
use DocumentStore\Exception\NotFoundException;

class DocumentStoreTest extends TestCase
{
    public function testSet(): void
    {
        @unlink($this->storage_path('books/demo.json'));

        $store = new DocumentStore($this->storage_path('books'));
        $document = new Document();
        $document->name = 'foo';
         
        $this->assertTrue($store->set('demo', $document));
        $this->assertFileExists($this->storage_path('books/demo.json'));
    }

    public function testSetDeep(): void
    {
        @unlink($this->storage_path('books/business/book.json'));

        $store = new DocumentStore($this->storage_path('books'));
        $document = new Document();
        $document->name = 'business book';
         
        $this->assertTrue($store->set('business/book', $document));
        $this->assertFileExists($this->storage_path('books/business/book.json'));
    }

    public function testInsert()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $document = new Document();
        $document->name = 'foo';

        $uuid = $store->insert($document);
        $this->assertNotEmpty($uuid);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{24}+/', $uuid);
        $this->assertEquals($uuid, $document->id());
        $this->assertEquals($uuid, $document->_id);

        $file = $this->storage_path('books/'. $document->_id . '.json');
        $this->assertFileExists($file);
        @unlink($file);
    }

    public function testInsertDeep()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $document = new Document();
        $document->name = 'foo';
  
        $uuid = $store->insert($document, ['prefix' => 'general']);
        $this->assertNotEmpty($uuid);

        $file = $this->storage_path('books/general/'. $document->_id . '.json');
        $this->assertFileExists($file);
        @unlink($file);
    }

    public function testHas(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->assertTrue($store->has('demo'));
        $this->assertFalse($store->has('does-not-exist'));
    }

    public function testHasDeep(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->assertTrue($store->has('business/book'));
        $this->assertFalse($store->has('business'));
    }

    public function testGet(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $document = $store->get('demo');
        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('foo', $document->name);
    }

    public function testGetDeep(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $document = $store->get('business/book');
        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('business book', $document->name);
    }

    public function testGetNotFound(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->expectException(NotFoundException::class);
        $store->get('does-not-exist');
    }

    public function testList()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $expected = ['business/book','demo'];
        $this->assertEquals($expected, $store->list());
    }

    public function testListNotRecursive()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $expected = ['demo'];
        $this->assertEquals($expected, $store->list('', false));
    }

    public function testListWithPath()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $expected = ['business/book'];
        $this->assertEquals($expected, $store->list('business'));
    }

    public function testListUnkownPath()
    {
        $store = new DocumentStore($this->storage_path('books'));
        $expected = [];
        $this->assertEquals($expected, $store->list('fiction'));
    }

    public function testDelete(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->assertTrue($store->delete('demo'));
    }

    public function testDeleteDeep(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->assertTrue($store->delete('business/book'));
    }

    public function testDeleteNotFound(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->expectException(NotFoundException::class);
        $this->assertTrue($store->delete('demo'));
    }

    public function testObjectId(): void
    {
        $store = new DocumentStore($this->storage_path('books'));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{24}+/', $store->objectId());
    }

    private function storage_path(string $path)
    {
        return sys_get_temp_dir()  .'/' . $path;
    }
}
