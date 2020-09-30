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

use DocumentStore\Assert;
use PHPUnit\Framework\TestCase;
use DocumentStore\DocumentStore;
use DocumentStore\Exception\DocumentStoreException;

class AssertTest extends TestCase
{
    public function testEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name' => 'Tony'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));
    }

    public function testNotEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name !=' => 'Tony'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));
    }

    public function testEqualsIn()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name' => ['Foo','Tony']
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));
    }

    public function testNotEqualsIn()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name !=' => ['Foo','Tony']
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));
    }

    public function testEqualsDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'emails' => 'cbear@hotmail.com'
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));
    }

    public function testEqualsInDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'emails' => ['foo','cbear@hotmail.com']
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));
    }

    public function testNotEqualsDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'emails !=' => 'cbear@hotmail.com'
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));
    }

    public function testNotEqualsInDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'emails !=' => ['foo','cbear@hotmail.com']
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));
    }

    public function testEqualsDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'addreses.street' => '25 corp road'
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));
    }

    public function testEqualsInDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'addreses.street' => ['foo','25 corp road']
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));
    }

    public function testNotEqualsDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'addreses.street !=' => '25 corp road'
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));
    }

    public function testNotEqualsInDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'addreses.street !=' => ['foo','25 corp road']
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));
    }

    public function testLike()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name LIKE' => '%o_y'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));
    }

    public function testNotLike()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'name NOT LIKE' => 'T_n%'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));
    }

    public function testGreaterThan()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'age >' => 30
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));

        // check invalid value
        $assert = new Assert([
            'age >' => 'a'
        ]);
        $this->assertFalse($assert->conditions($document));
    }

    public function testGreaterThanEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'age >=' => 32
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($assert->conditions($document));

        // check invalid value
        $assert = new Assert([
            'age >=' => 'a'
        ]);
        $this->assertFalse($assert->conditions($document));
    }

    public function testLessThan()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'age <' => 32
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));

        // check invalid value
        $assert = new Assert([
            'age <' => 'a'
        ]);
        $this->assertFalse($assert->conditions($document));
    }

    public function testLessThanEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $assert = new Assert([
            'age <=' => 25
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($assert->conditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($assert->conditions($document));

        // check invalid value
        $assert = new Assert([
            'age <=' => 'a'
        ]);
        $this->assertFalse($assert->conditions($document));
    }

    public function testParseConditionsInvalidOperator()
    {
        $this->expectException(DocumentStoreException ::class);
        $assert = new Assert([
            'name <-o->' => 'darthy'
        ]);
    }

    public function testParseConditionsInvalidLikeString()
    {
        $this->expectException(DocumentStoreException ::class);
        $assert = new Assert([
            'name LIKE' => ['%foo']
        ]);
    }

    private function fixture_path(string $path): string
    {
        return __DIR__ .'/Fixture/' . $path;
    }
}
