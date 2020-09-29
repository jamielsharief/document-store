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
which will generate an unique ID use this as the key and add this to a `_id` field.

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
$id = $store->insert($document, [
    'prefix' => 'computing/programming'
]); // computing/programming/5f70951f2c7d2d8ba290f708
```

## Searching

You can also search `Documents` in the `DocumentStore`, this is a file based searched for convienience. So if you have millions of Documents or perform large amounts of searches maybe an indexing software like `Elasticsearch` or `Solr` might be worth looking at.

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

Here are some examples how to search the fields

```
name // Tony Stark
emails // tony@stark.com
addresses.street // 25 corp road or 1000 malibu drive
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
$conditions = ['author !=' => ['Steven King','Dean Koontz']
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

You can use SQL LIKE and NOT LIKES using the wildcard `%` for any chars or `_` for just one character

```php
 $conditions = ['authors.name LIKE' =>'Tony%'];
 $conditions = ['author.name NOT LIKE' =>'%T_m%'];
 ```

### Finders

#### First

> First finder returns the actual Document if found and not the key, so this is more suitable for Documents added using insert

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

> All finder returns an array of Documents and not the keys, so this is more suitable for Documents added using insert

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