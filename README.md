# FB Request Monkey

Making batch and paged requests on the Facebook API can be complicated and annoying.  This app handles all of that complexity and allows
for dynamic, batching, paging and error handling

## Dependencies

Requires php underscore and the most recent version of the Facebook PHP SDK

## Installation

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



Then, install all the dependencies and start mongodb:

    $ cd attendance
    $ npm install
    $ sudo /etc/init.d/mongodb start  # or the variant for your system

If you encounter an error with bcrypt during `npm install` it's posible that
your openssl doesn't have the config files that are installed with 
`libssl-dev`, installing `libssl-dev` should fix that problem. If you're still
receving errors, try installing `node-gyp` with `sudo npm install -g node-gyp`.


Start the app with:

    $ node app.js