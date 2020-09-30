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

class DocumentDatabaseTest extends TestCase
{
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

    public function testInsertMany()
    {
        $db = new DocumentDatabase($this->storage_path('books'));
        
        $document1 = new Document(['name' => 'one']);
        $document2 = new Document(['name' => 'two']);

        $this->assertTrue($db->insertMany([$document1,$document2]));
        @unlink($this->storage_path('books/'. $document1->_id . '.json'));
        @unlink($this->storage_path('books/'. $document2->_id . '.json'));
    }

    public function update()
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

    public function updateMany()
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

    private function storage_path(string $path): string
    {
        return sys_get_temp_dir()  .'/' . $path;
    }
}
