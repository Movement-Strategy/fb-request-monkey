# FB Request Monkey

Making batch and paged requests on the Facebook API can be complicated and annoying.  This app handles all of that complexity and allows
for dynamic, batching, paging and error handling

## Dependencies

Requires php underscore and the most recent version of the Facebook PHP SDK

## Installation

```php
require('fb_request_monkey.php');
```

The Facebook API needs to be initialized with a config array before requests can be made.
	
### In Function
```php
 $fbConfig = array(
 	'appId' => 1000,
 	'secret' => 'asdfsds',
 	'cookie' => true,	
 );

 $results = FB_Request_Monkey::sendMany($actions, $fbConfig);
 ```
    
### In a Config File
```php	
FB_Request_Monkey::initialize($fbConfig);
```

## Usage

To make requests, build individual actions out of the query you want to make, the access token for that query, and the method.  The actions will be automatically batched.

```php
$actions = array(
	array(
		'query' => 'me/friends',
		'method' => 'GET',
		'token' => 'ADLFKJSDFS97823987'
		'params' => array(
			'param1' => 'test',
		),
	),
	array(
		'query' => 'me',
		'method' => 'GET',
		'token' => 'ADLFKJSDFS97823987'
		'params' => array(
			'param1' => 'test',
		),
	),
);

$results = FB_Request_Monkey::sendMany($actions);
```

### Single Action
```php

$action = array(
	'query' => 'me/friends',
	'token' => 'ADLFKJSDFS97823987'
	'method' => 'GET',
	'params' => array(
		'param1' => 'test',
	),
);

$results = FB_Request_Monkey::sendOne($action);
```

# Labelling / Grouping

If you want the data that's returned to be grouped, add a label parameter to the action.

```php
$actions = array(
	array(
		'query' => 'me/friends',
		'method' => 'GET',
		'token' => 'ADLFKJSDFS97823987'
		'params' => array(
			'param1' => 'test',
		),
		'label' => 'foo',
	),
	array(
		'query' => 'me',
		'method' => 'GET',
		'token' => 'ADLFKJSDFS97823987'
		'params' => array(
			'param1' => 'test',
		),
		'label' = 'bar',
	),
	$results = FB_Request_Monkey::sendMany($actions);
);
```
### Labelled Results

```php		
$labelledResults = array(
	'foo' => array(
		//data for this label
	),
	'bar' => array(
		//data for this label
	),
);
```