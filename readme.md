# Library

*The Library is a file-based, searchable data and file storage solution written in PHP.*

It's easy to setup and use. It's intended for cheap shared hosting, small virtual servers, your NAS or RaspberryPi and similar thingies where a node.js or Rails based solution is too much or simply not installable. 

It can handle a couple thousand entries without any problems. It's perfect for small personal single-user applications: a self-hosted photo album, a tweet backup, a personal checkins db, an address book, a personal invoice app, a book inventory, a todo app — you name it. 

The Library is also a perfect combination of any kind of text-based data combined with attached files. Each stored item can have any number of additional images, documents, videos, etc. attached. 

The Library has no schema. You can add any number of fields per item and individual items can have different sets of fields. 

***

## Table of contents

- [Philosophy](#philosophy)
- [Folder structure](#folder-structure)
- [Sqlite index](#sqlite-index)
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting started](#getting-started)
- [Creating items](#creating-items)
- [Item anatomy](#item-anatomy)
- [Item getters](#item-getters)
- [Additional item methods](#additional-item-methods)
- [Updates](#updates)
- [Queries](#queries)
- [Attachments](#attachments)
- [Deleting items](#deleting-items)
- [Roadmap](#roadmap)
- [License](#license)
- [Author](#author)

***

## Philosophy

- practical 
- simple
- robust
- low fancy-level
- high usefulness-level 
- for everyone

***

## Folder structure

The Library stores each item in its own folder. The folder structure follows the creation date of the item:

```
- library
-- 2015
--- 06
---- 10
----- 32-char-item-id
------ item.yaml
------ attachment-1.jpg
------ attachment-2.pdf
------ attachment-3.zip
etc. 
```

You can easily have multiple libraries per app and user in different folders. 

***

## SQLite Index

The Library uses a simple SQLite database as a searchable index and builds on the database class of the Kirby toolkit to provide a nice and clean query api. 

The index is stored in the main directory of the library and can be removed at any time. The Library will take care of rebuilding the index once it's gone. The folder structure, attachments and yaml files are always the original data source, which makes this solution very robust. 

***

## Requirements

- PHP 5.4+ 
- sqlite
- mbstring

***

## Installation

```
git clone https://github.com/bastianallgeier/library.git
```

***

## Getting started

```php
require('library/bootstrap.php');

$library = new Library(__DIR__ . '/mylibrary');
```

Make sure the library folder is writable. Otherwise the library will not be able to store any data for you.

***

## Creating items

```php
$item = $library->create('article', array(
  'title' => 'Hello World',
  'text'  => 'Lorem ipsum…'
));
```

*** 

## Item anatomy

Each item is stored in its own folder. The path to the folder follows the format YYYY/MM/DD/ID. The creation date is used herefor. Each item has the following default fields: 

```
- id (unique 32 char alphanumeric string)
- status (draft|public|private)
- type (can be any alphanumeric string between 2 and 32 characters)
- created (unix timestamp)
- updated (unix timestamp)
```

Additionally you can add any number of fields to an item.

*** 

## Item getters

```php
// standard getters
$item->id();
$item->status();
$item->type();
$item->created();
$item->updated();

// magic getters for your additional fields
$item->title();
$item->text();
// etc. 
```

*** 

## Additional item methods

```php
// i.e. 2012/12/12/5gWsOULCwbkInoVQbJ0MxcjybX5hfkv9
$item->path();
// i.e. 2012
$item->path('year');
// i.e. 2012/12
$item->path('month');
// i.e. 2012/12/12
$item->path('day');

// i.e. /var/www/library/2012/12/12/5gWsOULCwbkInoVQbJ0MxcjybX5hfkv9
$item->root();
// i.e. /var/www/library/2012
$item->root('year');
// i.e. /var/www/library/2012/12
$item->root('month');
// i.e. /var/www/library/2012/12/12
$item->root('day');

// Kirby toolkit folder object 
// http://getkirby.com/docs/toolkit/api#folder
$item->folder();

// returns an associative array of all item fields
$item->toArray();

// checks if the item exists
$item->exists();
```
Check the following examples for more specific stuff. 

***

## Updates

```php 
$item->update(array(
  'title' => 'New title',
  'text'  => 'Lorem ipsum dolor sit amet', 
  'date'  => time()
));
```

### Magic Setters

```php 
$item->title = 'New title';
$item->text  = 'Lorem ipsum…';

$item->store();
```

### Set method

```php
$item->set('title', 'New title');
$item->set('text', 'Lorem ipsum…');

$item->store();
```

### Setting multiple values

```php
$item->set(array(
  'title' => 'New title',
  'text'  => 'Lorem ipsum dolor sit amet'
));

$item->store();
```

### Modifying the type

```php
$item->type('blogpost');
```

### Modifying the creation date

```php
$item->created('2012-12-12 22:33');
```

### Switching the status

```php
$item->status('public');
```

Available statuses: draft (default), public, private

***

## Queries

### Finding a single item by id

```php
$item = $library->find('ekM9AZMIWbkm48hlpRCJO52FVCQSkClL');
```

### All items from the library

```php
$items = $library->all();
```

### Pagination

```php
$items = $library->page($page, $limit);
```

### Counting all items

```php 
$count = $library->count();
```

### Filtering the library by type

```php 
$items = $library->type('article')->all();
$items = $library->type('article')->page($page, $limit);
$count = $library->type('article')->count();
```

### Filtering the library by status

```php 
$items = $library->status('draft')->all();
$items = $library->status('public')->page($page, $limit);
$count = $library->status('private')->count();
```

### Filtering the library by year

```php 
$items = $library->year('2015')->all();
$items = $library->year('2015')->page($page, $limit);
$count = $library->year('2015')->count();
```

### Filtering the library by month

```php 
$items = $library->month('2015-06')->all();
$items = $library->month('2015-06')->page($page, $limit);
$count = $library->month('2015-06')->count();
```

### Filtering the library by day

```php 
$items = $library->day('2015-06-10')->all();
$items = $library->day('2015-06-10')->page($page, $limit);
$count = $library->day('2015-06-10')->count();
```

### Searching the library

```php 
$items = $library->search($query)->all();
$items = $library->search($query)->page($page, $limit);
$count = $library->search($query)->count();
```

### Combining filters

```php 
$items = $library->year('2015')->type('article')->status('public')->search($query)->all();
$items = $library->year('2015')->type('article')->status('public')->search($query)->page($page, $limit);
$count = $library->year('2015')->type('article')->status('public')->search($query)->count();
```

***

## Attachments

### Attaching a file via URL

```php
$item->attach('http://example.com/image.jpg');
```

### Attaching a file from the file system

```php
$item->attach('/some/path/image.jpg');
```

### Setting a custom filename

```php
$item->attach('/some/path/image.jpg', 'myimage.jpg');
```

### Fetching attachments for an item

```php 
// all files
$files  = $item->files();

// all images
$images = $item->images();

// all videos
$videos = $item->videos();

// all documents
$documents = $item->documents();
```

All methods above return a Kirby Toolkit Collection of Media objects:
- <http://getkirby.com/docs/toolkit/api#collection>
- <http://getkirby.com/docs/toolkit/api#media>

### Deleting an attachment

```php
$item->detach('myimage.jpg);
```

***

## Deleting items

### Deleting a single item

```php
$item->delete();
```

## Deleting multiple items from the library

```php
$library->type('article')->delete();
```

***

## Roadmap 

- more docs
- "between" query method for dates
- maybe geo queries
- Travis setup
- Composer support

***

## License

<http://www.opensource.org/licenses/mit-license.php>

***

## Author

Bastian Allgeier 
- <https://bastianallgeier.com>   
- <bastian@getkirby.com>   
- <https://twitter.com/bastianallgeier>
