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

use PHPUnit\Framework\TestCase;
use DocumentStore\DocumentStore;
use DocumentStore\DocumentFinder;
use DocumentStore\Exception\DocumentStoreException;

class DocumentFinderTest extends TestCase
{
    public function testEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name' => 'Tony'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testNotEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name !=' => 'Tony'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testEqualsIn()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name' => ['Foo','Tony']
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testNotEqualsIn()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name !=' => ['Foo','Tony']
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testEqualsDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'emails' => 'cbear@hotmail.com'
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testEqualsInDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'emails' => ['foo','cbear@hotmail.com']
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testNotEqualsDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'emails !=' => 'cbear@hotmail.com'
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testNotEqualsInDeep()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'emails !=' => ['foo','cbear@hotmail.com']
        ]);

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testEqualsDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'addreses.street' => '25 corp road'
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testEqualsInDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'addreses.street' => ['foo','25 corp road']
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testNotEqualsDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'addreses.street !=' => '25 corp road'
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testNotEqualsInDeepArray()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'addreses.street !=' => ['foo','25 corp road']
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testLike()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name LIKE' => '%o_y'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testNotLike()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'name NOT LIKE' => 'T_n%'
        ]);
    
        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));
    }

    public function testGreaterThan()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'age >' => 30
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));

        // check invalid value
        $finder = new DocumentFinder([
            'age >' => 'a'
        ]);
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testGreaterThanEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'age >=' => 32
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertTrue($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertFalse($finder->assertConditions($document));

        // check invalid value
        $finder = new DocumentFinder([
            'age >=' => 'a'
        ]);
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testLessThan()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'age <' => 32
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));

        // check invalid value
        $finder = new DocumentFinder([
            'age <' => 'a'
        ]);
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testLessThanEquals()
    {
        $store = new DocumentStore($this->fixture_path('contacts'));

        $finder = new DocumentFinder([
            'age <=' => 25
        ]);

        $document = $store->get('5f731f1345b65d076150a7b6');
        $this->assertFalse($finder->assertConditions($document));

        $document = $store->get('5f7320135e9d29aebacd4f97');
        $this->assertTrue($finder->assertConditions($document));

        // check invalid value
        $finder = new DocumentFinder([
            'age <=' => 'a'
        ]);
        $this->assertFalse($finder->assertConditions($document));
    }

    public function testParseConditionsInvalidOperator()
    {
        $this->expectException(DocumentStoreException ::class);
        $finder = new DocumentFinder([
            'name <-o->' => 'darthy'
        ]);
    }

    public function testParseConditionsInvalidLikeString()
    {
        $this->expectException(DocumentStoreException ::class);
        $finder = new DocumentFinder([
            'name LIKE' => ['%foo']
        ]);
    }

    private function fixture_path(string $path): string
    {
        return __DIR__ .'/Fixture/' . $path;
    }
}
