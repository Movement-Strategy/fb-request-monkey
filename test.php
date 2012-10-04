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
		
		$actions = __::map($users, function($user) {
			return array(
				'token' => $user['token'],
				'query' => 'me',
				'method' => 'GET',
				'label' => $user['id'],
				'params' => array(
					'input_token' => $user['token'],
				),
			);
		});
				
/*
		$actions = array();
		$i = 1;
		while ($i <= 4) {
			$label1 = $i % 2 == 0 ? 'query1' : 'query2';
			$label2 = $i + 1000;
			$actionToAdd = $action;
			$actionToAdd['label'] = array($label2, $label1);
			array_push($actions, $actionToAdd);
			$i++;
		}	
*/


		$options = array(
			'allowErrors' => true,
		);
		
		$data = FB_Request_Monkey::sendMany($actions, $config, $options);
		echo json_encode($data);


	
