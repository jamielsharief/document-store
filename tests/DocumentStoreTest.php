<?php
/**
 * DocumentStore
 * Copyright 2020 Jamiel Sharief.
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

use BadMethodCallException;
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
        $this->assertEquals('demo', $document->key());
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
    
    public function testSearch()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $documents = $store->search([
            'conditions' => [
                'author' => 'Mark Minervini'
            ]
        ]);
        $this->assertNotEmpty($documents);
        $this->assertEquals('5f730fd6ed12968109f89d0d', $documents[0]);
    }

    public function testFindFirst()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $document = $store->find('first', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ]
        ]);
        $this->assertEquals('5f730fd6ed12968109f89d0d', $document->_id);
    }

    public function testFindFirstNoResult()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('first', [
            'conditions' => [
                'author' => 'Steven King'
            ]
        ]);
        $this->assertNull($result);
    }

    /**
     * @depends testFindFirst
     */
    public function testFindFirstOffset()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $document = $store->find('first', [
            'conditions' => [
                'author' => 'Mark Minervini',
            ],
            'offset' => 1
        ]);
        $this->assertEquals('5f730fde23b43a7800f7da25', $document->_id);
    }

    public function testFindAll()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $documents = $store->find('all', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ]
        ]);
        $this->assertNotEmpty($documents);
        $this->assertEquals('5f730fd6ed12968109f89d0d', $documents[0]->_id);
        $this->assertEquals('5f730fde23b43a7800f7da25', $documents[1]->_id);
    }

    public function testFindAllNoResult()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('all', [
            'conditions' => [
                'author' => 'Steven King'
            ]
        ]);
        $this->assertEquals([], $result);
    }

    public function testFindAllOffset()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $documents = $store->find('all', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ],
            'offset' => 1
        ]);
        $this->assertNotEmpty($documents);
        $this->assertEquals('5f730fde23b43a7800f7da25', $documents[0]->_id);
        $this->assertEquals('5f730ff3ee3060441a0744dc', $documents[1]->_id);
    }

    public function testFindAllLimit()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $documents = $store->find('all', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ],
            'offset' => 1,
            'limit' => 1
        ]);
        $this->assertNotEmpty($documents);
        $this->assertEquals('5f730fde23b43a7800f7da25', $documents[0]->_id);
        $this->assertCount(1, $documents);
    }

    public function testFindList()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $this->assertEquals(
            [
                '5f730fd6ed12968109f89d0d',
                '5f730fde23b43a7800f7da25',
                '5f730ff3ee3060441a0744dc'
            ],
            $store->find('list', [
                'conditions' => [
                    'author' => 'Mark Minervini'
                ]
            ])
        );
    }

    public function testFindListNoResult()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('list', [
            'conditions' => [
                'author' => 'Steven King'
            ]
        ]);
        $this->assertEquals([], $result);
    }

    public function testFindListOffset()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $this->assertEquals(
            [
                '5f730fde23b43a7800f7da25',
                '5f730ff3ee3060441a0744dc'
            ],
            $store->find('list', [
                'conditions' => [
                    'author' => 'Mark Minervini'
                ],
                'offset' => 1
            ])
        );
    }

    public function testFindListOffsetLimit()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $this->assertEquals(
            [
                '5f730fde23b43a7800f7da25',
            ],
            $store->find('list', [
                'conditions' => [
                    'author' => 'Mark Minervini'
                ],
                'offset' => 1,
                'limit' => 1
            ])
        );
    }

    public function testFindCount()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('count', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ]
        ]);
        $this->assertEquals(3, $result);
    }

    /**
     * Seems bizzare but there is probably a use case
     *
     * @return void
     */
    public function testFindCountLimit()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('count', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ],
            'limit' => 2
        ]);
        $this->assertEquals(2, $result);
    }

    public function testFindCountOffset()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('count', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ],
            'offset' => 2
        ]);
        $this->assertEquals(1, $result);
    }

    public function testFindCountOffsetLimit()
    {
        $store = new DocumentStore($this->fixture_path('books'));

        $result = $store->find('count', [
            'conditions' => [
                'author' => 'Mark Minervini'
            ],
            'offset' => 1,
            'limit' => 3
        ]);
        $this->assertEquals(2, $result);
    }

    public function testFinderNotExist()
    {
        $store = new DocumentStore($this->fixture_path('books'));
        $this->expectException(BadMethodCallException::class);
        $store->find('publishable');
    }

    private function storage_path(string $path): string
    {
        return sys_get_temp_dir()  .'/' . $path;
    }
    
    private function fixture_path(string $path): string
    {
        return __DIR__ .'/Fixture/' . $path;
    }
}
