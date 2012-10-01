<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

require_once('fb_request_monkey.php');
require_once('../libs/underscore/underscore.php');
require_once('../libs/fb_sdk/facebook.php');


			
		
		$config = array(
			'appId' => 103973243026241,
			'secret' => '16ae668521de9cb99d76761bc529fbe9',
			'cookie' => false,
		);
		
		$action = array(
			'method' => 'GET',
			'query' => 'mo/friends',
			'token' => 'AAACZAvGW91SwBAAwx0d8DKTpkwkZCXP2yvF5UK2YNPYJVcDThI7HTFImTutxXrJQH2icFSLZBIkwOr4qD0SxUnMD01rFQJYgNZCfpgFh1wZDZD',
			'params' => array(
				'limit' => 5,
			),
		);
		
		$actions = array();
		$i = 1;
		while ($i <= 4) {
			$label1 = $i % 2 == 0 ? 'query1' : 'query2';
			$label2 = $i + 1000;
			$actionToAdd = $action;
			$actionToAdd['label'] = array($label1, $label2);
			array_push($actions, $actionToAdd);
			$i++;
		}
		$options = array(
			'allowErrors' => true,
		);

		
		$data = FB_Request_Monkey::sendMany($actions, $config, $options);
		echo json_encode($data);


	