# DocumentStore

DocumentStore is a Key-Value Store (KVS) for storing data documents which can have the same or varying fields and works with arrays and nested data.

## Creating a Document

Create a `Document` and add any data you like, it can be nested include arrays

```php
use DocumentStore\Document;

$document = new Document();
$document->title = 'Patterns of Enterprise Application Architecture';
$document->author = 'Martin Fowler';
$document->type = 'hardcover';
$document->isbn = [
    '978-0321127426',
    '0321127420'
];
```

You can also create a `Document` from an array

```php
use DocumentStore\Document;

$document = new Document([
    'title' => 'Patterns of Enterprise Application Architecture',
    'author' => 'Martin Fowler',
    'type' => 'hardcover',
    'isbn' => [
        '978-0321127426',
        '0321127420'
    ]
]);
```

The `Document` object can also be accessed as an array

```php
echo $document['title'];
$document['description'] = null;
unset($document['something']);
```

## Set

To add a `Document` to the `DocumentStore`

```php
use DocumentStore\DocumentStore;
use DocumentStore\Document;

$store = new DocumentStore(storage_path('books'));

$document = new Document();
$document->title = 'Patterns of Enterprise Application Architecture';
$document->author = 'Martin Fowler'
$document->type = 'hardcover';
$document->isbn = [
    '978-0321127426',
    '0321127420'
];

$store->set('0321127420', $document);
```

You can also group `Documents` using a prefixes (as many levels) which organizes the `Documents` into folders.

```php
$store->set('programming/0321127420', $document);
```

## Get

To get a `Document` from the `DocumentStore`

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$document = $store->get('0321127420');

echo $document->title;
```

## Has

To check a `Document` exists

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$result = $store->has('0321127420');
```

## Delete

To delete a `Document` from the `DocumentStore`

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$store->delete('0321127420');
```

## List

To list `Documents` in the `DocumentStore`

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$list = $store->list(); // ['programming/0321127420']
$list = $store->list('programming'); // ['programming/0321127420']
```

## Inserting 

If you need the `Document` to have its own ID then you can use the `insert` function,
which will generate an UUID use this as the key and add this to a `_id` field.

```php
use DocumentStore\DocumentStore;
use DocumentStore\Document;

$store = new DocumentStore(storage_path('books'));

$document = new Document();
$document->title = 'Patterns of Enterprise Application Architecture';
$document->author = 'Martin Fowler';
$document->type = 'hardcover';
$document->isbn = '0321127420';

$id = $store->insert($document);
```

It will return the ID which was also used as the key, we can can then retrieve the Document

```php
print_r($store->get($id));

/*
DocumentStore\Document Object
(
    [_id] => 5f70951f2c7d2d8ba290f708
    [title] => Patterns of Enterprise Application Architecture
    [author] => Martin Fowler
    [type] => hardcover
    [isbn] => 0321127420
)
*/
```

You can also insert into a particular path

```php
$id = $store->insert($document,[
    'prefix' => 'computing/programming'
]); // computing/programming/5f70951f2c7d2d8ba290f708
```