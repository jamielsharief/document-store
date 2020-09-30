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

class FunctionsTest extends TestCase
{
    public function testSet()
    {
        $filename = sys_get_temp_dir() . '/document-store/test.php';
        @unlink($filename);

        $document = new Document();
        $document->name = 'test';

        $this->assertTrue(cache_set('test', $document));
    }

    public function testGet()
    {
        $this->assertInstanceOf(Document::class, cache_get('test'));

        $this->assertNull(cache_get('foo'));
    }

    public function testHas()
    {
        $this->assertFalse(cache_has('foo'));
        $this->assertTrue(cache_has('test'));
    }

    /**
     * @depends testSet
     */
    public function testDelete()
    {
        $this->assertTrue(cache_set('delete', new Document(['name' => 'delete'])));
        $this->assertTrue(cache_has('delete'));
        $this->assertTrue(cache_delete('delete'));
        $this->assertFalse(cache_has('delete'));
        $this->assertFalse(cache_delete('delete'));
    }

    public function testClear()
    {
        $this->assertTrue(cache_has('test'));
        cache_clear();
        $this->assertFalse(cache_has('test'));
    }
}
