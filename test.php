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
			'query' => 'me/friends',
			'token' => 'AAACZAvGW91SwBAAwx0d8DKTpkwkZCXP2yvF5UK2YNPYJVcDThI7HTFImTutxXrJQH2icFSLZBIkwOr4qD0SxUnMD01rFQJYgNZCfpgFh1wZDZD',
			'params' => array(
				'limit' => 5,
			),
		);
		
		$actions = array();
		$i = 1;
		while ($i <= 2) {
			$actionToAdd = $action;
			$actionToAdd['label'] = $i + 1000;
			array_push($actions, $actionToAdd);
			$i++;
		}
		$options = array(
			'returnBatchErrors' => true,
		);
		
		$data = FB_Request_Monkey::sendMany($actions, $config, $options);
		echo json_encode($data);