# DocumentStore

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://github.com/jamielsharief/document-store/workflows/CI/badge.svg)](https://github.com/jamielsharief/document-store/actions)
[![coverage](https://coveralls.io/repos/github/jamielsharief/document-store/badge.svg?branch=master)](https://coveralls.io/github/jamielsharief/document-store?branch=master)

DocumentStore is a Key-Value Store (KVS) that stores data in JSON documents, giving you a productive way to work with simple or nested data and allows for felxible and dynamic schema. Data can easily be synced across multiple servers. This provides a consistent interface for working with JSON data as a no SQL database.

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
$document->author = 'Martin Fowler';
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

To get a `Document` with a prefix

```php
$document = $store->get('programming/0321127420');
```

## Has

To check a `Document` exists

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$result = $store->has('0321127420');
```

To check a `Document` with a prefix

```php
$store->has('programming/0321127420');
```

## Delete

To delete a `Document` from the `DocumentStore`

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$store->delete('0321127420');
```

To delete a `Document` with a prefix

```php
$store->delete('programming/0321127420');
```

## List

To list `Documents` in the `DocumentStore`

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$list = $store->list(); // ['programming/0321127420']
$list = $store->list('programming'); // ['programming/0321127420']
```

## Document


### Key

If you are working a `Document` that was stored in the `DocumentStore`, you can use the `key` method
to get the key that was used. This is handy for when working with search results from find first or find all.

```php
$key = $document->key();
```


### To Array

To convert the `Document` into an array

```php
$document->toArray();
```

#### To Json

To convert the `Document` into a JSON string

```php
$document->toJson();
$document->toJson(['pretty' => true]);
```

## Searching

You can also search `Documents` in the `DocumentStore`, this is a file based searched . So if you have 100,000s of Documents in a prefix or regularly perform large amounts of searches and speed is an issue,  maybe an indexing software like `Elasticsearch` or `Solr` might be worth looking at.

### Keys

To get a list a keys that match a set of conditions (see below). There are also finders, which are detailed after conditions.

```php
use DocumentStore\DocumentStore;
$store = new DocumentStore(storage_path('books'));

$list = $store->search([
    'conditions' => ['author' => 'Dean Koontz'],
    'prefix' => 'fiction/horror', // optional
    'limit' => 10,  // limit the number of results
]);
```

The following options can be passed

- prefix: to search a given prefix e.g. computers/development
- conditions: an array of conditions
- limit: the maximum number of results to find
- offset: the offset from which matching record to find. This is handy when you want to paginate results using limit

### Fields

You can search different fields using the dot notation, and you can searched nested data as well.

Lets say you have this data

```json
{
    "name": "Tony Stark",
    "emails": [
        "tony@stark.com"
    ],
    "addreses": [
        {
            "street": "1000 malibu drive",
            "state": "california",
            "country": "US"
        },
        {
            "street": "25 corp road",
            "state": "california",
            "country": "US"
        }
    ],
    "status": "new"
}
```

Here are some examples how to search the the different levels of fields;

```php
$conditions = [
    'name' => 'Tony Stark' // searches string
    'emails' => 'tony@stark.com', // searches data array
    'addresses.street' => '1000 malibu drive' // searches the street fields in addresses etc
];
```

### Conditions

Here are some examples on the different search conditions

#### Equals

You can check single or multiple values (IN)

```php
$conditions = ['author' => 'Dean Koontz']
$conditions = ['author' => ['Steven King','Dean Koontz']
```

#### Not Equals

You can check single or multiple values (NOT IN)

```php
$conditions = ['author !=' => 'Dean Koontz']
$conditions = ['author !=' => ['Steven King','Dean Koontz']]
```

#### Arithmetic

To check field values, such as greater, less than etc.

```php
$conditions = ['age >' 21];
$conditions = ['age >=' 21];
$conditions = ['age <' 50];
$conditions = ['age <=' 50];
```

#### Like

You can use SQL LIKE and NOT LIKES using the wildcard `%` for any chars or `_` for just one character, this is a case insensitive search.

```php
 $conditions = ['authors.name LIKE' =>'Tony%'];
 $conditions = ['author.name NOT LIKE' =>'%T_m%'];
 ```

### Finders

You can use the `key` method on any document found using `first` or `all` to find what key was used to save that `Document`.

#### First

To find the first `Document` that matches a set of conditions

```php
$result = $store->find('first', [
    'conditions' => [
        'author' => ['Mark Minervini']
    ],
]);
/*
DocumentStore\Document Object
(
    [_id] => 5f730fd6ed12968109f89d0d
    [title] => Trade Like a Stock Market Wizard: How to Achieve Super Performance in Stocks in Any Market
    [author] => Mark Minervini
)
*/

```

### All


To find all `Documents` that match a set of conditions

```php
$result = $store->find('all', [
    'conditions' => [
        'author' => ['Mark Minervini']
    ],
    'limit' => 2,
]);
/*
Array
(
    [0] => DocumentStore\Document Object
        (
            [_id] => 5f730fd6ed12968109f89d0d
            [title] => Trade Like a Stock Market Wizard: How to Achieve Super Performance in Stocks in Any Market
            [author] => Mark Minervini
        )
    [1] => DocumentStore\Document Object
        (
            [_id] => 5f730fde23b43a7800f7da25
            [title] => Mindset Secrets for Winning: How to Bring Personal Power to Everything You Do
            [author] => Mark Minervini
        )
)
*/
```


### Count

> If you only want the count then use this but if you want the count and records, count the results manually from search or the all finder, to prevent repetitive and pointless searching on the hard drive.

To find a count `Documents` that match a set of conditions

```php
$count = $store->find('count', [
    'conditions' => [
        'author' => ['Mark Minervini']
    ],
]); // 3
```

## Document Database

There is a `DocumentDatabase` adapter which uses the `DocumentStore` as the backend, but makes it work similar to a database, such as generating keys, saving multiple records etc. 

## Insert

Insert first generates a unique ID, adds an `_id` field  to the `Document`, then adds this to the `DocumentStore` using the ID as the key.

```php
use DocumentStore\DocumentDatabase;
use DocumentStore\Document;

$db = new DocumentDatabase(storage_path('books'));

$document = new Document();
$document->title = 'Patterns of Enterprise Application Architecture';
$document->author = 'Martin Fowler';
$document->type = 'hardcover';
$document->isbn = '0321127420';

$db->insert($document);

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
$db->insert($document, [
    'prefix' => 'computing/programming'
]); // computing/programming/5f70951f2c7d2d8ba290f708
```


There is also an `insertMany` method

```php
$db->insertMany([$document]);
```

## Update

When you are working with `Documents` that have already been saved in the `DocumentDatabase`, and have an `_id` field you can save any changes using the `update` method.

```php
$db->update($document);
```

There is also an `updateMany` method

```php
$db->updateMany([$document]);
```