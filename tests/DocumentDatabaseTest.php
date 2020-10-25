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

use DocumentStore\Document;
use PHPUnit\Framework\TestCase;
use DocumentStore\DocumentDatabase;
use DocumentStore\Exception\DocumentStoreException;

class DocumentDatabaseTest extends TestCase
{
    use DocumentStoreTestTrait;

    public function testInsert()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $document = new Document();
        $document->name = 'foo';

        $this->assertTrue($db->insert($document));
      
        $this->assertMatchesRegularExpression('/^[0-9a-f]{24}+/', $document->_id);
    
        $file = $this->storage_path('books/'. $document->_id . '.json');
        $this->assertFileExists($file);
        @unlink($file);
    }

    public function testInsertFail()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->assertFalse($db->insert(new Document(['_id' => 1234])));
    }

    public function testInsertMany()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        
        $document1 = new Document(['name' => 'one']);
        $document2 = new Document(['name' => 'two']);

        $this->assertTrue($db->insertMany([$document1,$document2]));
        @unlink($this->storage_path('books/'. $document1->_id . '.json'));
        @unlink($this->storage_path('books/'. $document2->_id . '.json'));
    }

    public function testInsertErrorSaving()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->expectException(DocumentStoreException::class);
        $document = new Document(['_id' => 1234]);
        $this->assertFalse($db->insertMany([$document]));
    }

    public function testInsertManyException()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->expectException(DocumentStoreException::class);
        $db->insertMany(['foo']);
    }

    public function testUpdate()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $document = new Document(['name' => 'test']);

        $this->assertFalse($db->update($document));

        $this->assertTrue($db->insert($document));
        $document->name = 'test-changed';

        $this->assertTrue($db->update($document));

        $this->assertEquals('test-changed', $db->get($document->_id)->name);

        @unlink($this->storage_path('books/'. $document->_id . '.json'));
    }

    public function testUpdateFail()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->assertFalse($db->update(new Document(['name' => 'no_id'])));
    }

    public function testUpdateMany()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        
        $document1 = new Document(['name' => 'one']);
        $document2 = new Document(['name' => 'two']);

        $this->assertTrue($db->insertMany([$document1,$document2]));

        $document1->name = 'one-changed';
        $document2->name = 'two-changed';
        $this->assertTrue($db->updateMany([$document1,$document2]));

        $this->assertEquals('one-changed', $db->get($document1->_id)->name);
        $this->assertEquals('two-changed', $db->get($document2->_id)->name);

        @unlink($this->storage_path('books/'. $document1->_id . '.json'));
        @unlink($this->storage_path('books/'. $document2->_id . '.json'));
    }

    public function testUpdateManyInvalidDocument()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->expectException(DocumentStoreException::class);
        $db->updateMany(['foo']);
    }

    public function testUpdateManyNotInDatabase()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        $this->expectException(DocumentStoreException::class);
        $db->updateMany([new Document(['name' => 'not in database'])]);
    }

    private function storage_path(string $path): string
    {
        return sys_get_temp_dir()  .'/' . $path;
    }
}
