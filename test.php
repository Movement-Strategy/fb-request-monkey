<?php

error_reporting(E_ALL); 
ini_set( 'display_errors','1');

require_once('fb_request_monkey.php');
require_once('../libs/underscore/underscore.php');
require_once('../libs/fb_sdk/facebook.php');


	$fbConfig = array(
		'appId' => 169034439841068,
		'secret' => '16ae668521de9cb99d76761bc529fbe9',
		'cookie' => true,
	);
		
	$accessToken = 'AAACZAvGW91SwBAAwx0d8DKTpkwkZCXP2yvF5UK2YNPYJVcDThI7HTFImTutxXrJQH2icFSLZBIkwOr4qD0SxUnMD01rFQJYgNZCfpgFh1wZDZD';
	
	$actionCount = 1;
	
	$testAction = array(
	    'method' => 'GET',
	    'query' => 'act_113004955487436/adgroups',
	    'token' => 'AAACZAvGW91SwBAAwx0d8DKTpkwkZCXP2yvF5UK2YNPYJVcDThI7HTFImTutxXrJQH2icFSLZBIkwOr4qD0SxUnMD01rFQJYgNZCfpgFh1wZDZD',
	);		
	
	$accounts = array(
		'105056166273966',
		'114445558699214',
		'104058953017112'
	);
	
	$actions = array();
	$i = 0;
	$currentActionCount = 0;
	while($currentActionCount < $actionCount) {
		foreach($accounts as $account) {
			$actions[$i] = array(
			    'method' => 'GET',
			    'query' => "act_$account/adcampaigns",
			    'token' => 'AAACZAvGW91SwBAAwx0d8DKTpkwkZCXP2yvF5UK2YNPYJVcDThI7HTFImTutxXrJQH2icFSLZBIkwOr4qD0SxUnMD01rFQJYgNZCfpgFh1wZDZD',
/* 			    'label' => 'act_' . $account, */
			);
			$i++;
		}
		$currentActionCount++;
	}
	
	$results = FB_Request_Monkey::sendMany($actions, $fbConfig);
/*
	$output = $output = __::map($results, function($result, $account) {
		$flattenedResult = __::flatten($result, true);
		return count($flattenedResult);
	});
*/
	
		
	echo json_encode($results);
	
	
	
	

	
