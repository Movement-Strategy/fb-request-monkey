<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

require_once('fb_request_monkey.php');
require_once('../libs/underscore/underscore.php');
require_once('../libs/fb_sdk/facebook.php');


		
	$users = array(
		array(
			'token' => 'AAAGClxpgGZBEBAJnG5SwMbtMc74bdqmmZC6mhKhvrCdf9WzXqTmuyhTGcmN1vDvG4htjRm9NCDdC4zHCWFR8ZCyOHOARIszz8xOIreQ5wZDZD',
			'id' => 26204911,
		),
		// valid
		array(
			'token' => 'AAAGClxpgGZBEBAB4OmfSfFZB9ifgVr7fZBppvKnrCXvZCeW7dHQLx8lJj4elp6g4ZCzV2IaF5EwnAV6Bgp0vvey0ulEqZAgIvBvFTGP5jSKQZDZD',
			'id' => 645767981,
		),
	);	
	
	
	// these are not switched
	
	
		
		$config = array(
			'appId' => 425060470889441,
			'secret' => '4dc0f0c979cf69fc82241284e0ab3aa2',
			'cookie' => true,
		);
		
				
		
		function buildActions($userCount, $connectionCount) {
			$user = array(
				'token' => 'AAAGClxpgGZBEBAJnG5SwMbtMc74bdqmmZC6mhKhvrCdf9WzXqTmuyhTGcmN1vDvG4htjRm9NCDdC4zHCWFR8ZCyOHOARIszz8xOIreQ5wZDZD',
				'id' => 26204911,
			);
			$actions = array();
			$i = 1;
			$userAction = array(
				'token' => $user['token'],
				'query' => 'me',
				'method' => 'GET',
				'label' => array($user['id'], 'core'),
			);
			
			$connectionAction = array(
				'token' => $user['token'],
				'query' => 'me/friends',
				'method' => 'GET',
				'label' => array($user['id'], 'likes'),
			);
			
			while ($i <= $userCount) {
				array_push($actions, $userAction);
				$i++;
			}
			$i = 1;
			
			while ($i <= $connectionCount) {
				array_push($actions, $connectionAction);
				$i++;
			}
			
			return $actions;
		}
			


		$options = array(
/* 			'allowErrors' => true, */
		);
		
		$actions = buildActions(1, 10);
		
		$data = FB_Request_Monkey::sendMany($actions, $config, $options);
		$test = FB_Request_Monkey::$testArray;
/* 		echo json_encode($data); */


	
