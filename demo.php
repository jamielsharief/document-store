<?php

use DocumentStore\Document;
use DocumentStore\DocumentStore;
use DocumentStore\DocumentDatabase;

require __DIR__ . '/vendor/autoload.php';

$store = new DocumentStore(__DIR__ . '/data/books');

$document = new Document();
$document->title = 'Patterns of Enterprise Application Architecture';
$document->author = 'Martin Fowler';
$document->type = 'hardcover';
$document->isbn = [
    '978-0321127426',
    '0321127420'
];

$store->set('computing/programming/0321127420', $document);

print  PHP_EOL . 'store:list' . PHP_EOL;
print_r($store->list());

$result = $store->get('computing/programming/0321127420');

print PHP_EOL . 'store:get' . PHP_EOL;
print_r($result);

$db = new DocumentDatabase(__DIR__ . '/data/database');
$db->insert($document);

$id = $document->_id;

print PHP_EOL . 'db' . PHP_EOL;
print_r($db->get($id));
