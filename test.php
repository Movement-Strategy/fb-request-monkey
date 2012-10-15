<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

require_once('fb_request_monkey.php');
require_once('../libs/underscore/underscore.php');
require_once('../libs/fb_sdk/facebook.php');


		
	$users = array(
		// invalid
		array(
			'token' => 'AAADNISLEU9oBACz88GSSSlx34sMzyXiVTSfQ5kMAWS33wHsMcjkkM9LzC8VUtYz4DZCjQgYkAvdaWwKPgISFgJtOhYCEam1IRm2wtZCeLflvtLvr41',
			'id' => 1046940381,
		),		
		array(
			'token' => 'AAADNISLEU9oBAH29WP1Dg9PIk97KqaShHf0lPfDZAeRq7DPWhx4ZAwvAspQpfwe2xWmjQBNw11ZCa49RzWi11uEzq3y0FBUgBQ0PZApmzwZDZD',
			'id' => 678234993,
		),
		// valid
		array(
			'token' => 'AAADNISLEU9oBAAwOW8Jpt5RZCPlwOKeYEnjpwCsLh31CYE53cSoy6jvJpxwW6ExJWKqbH460yTfSpEXdEssAZAEmPWPSLCqZBdDSc6iKgZDZD',
			'id' => 1762732006,
		),
	);	
	
	
	// these are not switched
	
	
		
		$config = array(
			'appId' => 225542200906714,
			'secret' => '0b60e50aded2e11f0e389c50d3d5fa8b',
			'cookie' => true,
		);
		
				
		
		function buildActions($userCount, $connectionCount) {
			$user = array(
				'token' => 'AAADNISLEU9oBAH29WP1Dg9PIk97KqaShHf0lPfDZAeRq7DPWhx4ZAwvAspQpfwe2xWmjQBNw11ZCa49RzWi11uEzq3y0FBUgBQ0PZApmzwZDZD',
				'id' => 678234993,
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
				'query' => 'me/likes',
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
		
		$actions = buildActions(1, 1);
		
		$data = FB_Request_Monkey::sendMany($actions, $config, $options);
		echo json_encode($data);


	
