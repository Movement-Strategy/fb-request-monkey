<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

require_once('fb_request_monkey.php');
require_once('../libs/underscore/underscore.php');
require_once('../libs/fb_sdk/facebook.php');


		
/*
$action = array(
    'method' => 'GET',
    'query' => 'fql',
    'token' => 'AAAHCqE1ZBYBgBAPKcrI8oZCJcqWOBzCAGsmg3OiBLGZCHePMFApX6PDvZA7aRRZCGpiiNr0o7dOZB1xgcJqLZBro88OydIXlONPnRMqn8qLQgZDZD',
    'params' => array(
        'q' => array(
            'query1' => 'SELECT uid2 FROM friend WHERE uid1 = me()',
            'query2' => 'SELECT name FROM user WHERE uid IN (SELECT uid2 FROM #query1)',
        ),
    ),
);		
*/

	$action = array(
	    'method' => 'GET',
	    'query' => 'fql',
	    'token' => 'AAAHCqE1ZBYBgBAPKcrI8oZCJcqWOBzCAGsmg3OiBLGZCHePMFApX6PDvZA7aRRZCGpiiNr0o7dOZB1xgcJqLZBro88OydIXlONPnRMqn8qLQgZDZD',
	    'params' => array(
	        'q' => 'SELECT attachment,message FROM stream WHERE source_id = me() LIMIT(50)',
	    ),
	);		

	$fbConfig = array(
		'appId' => 495503087132696,
		'secret' => '761f88f917529c3a30dbbd0cb60dac89',
		'cookie' => true,
	);
	$results = FB_Request_Monkey::sendOne($action, $fbConfig);
	
	echo json_encode($results);
	
	
	
	

	
