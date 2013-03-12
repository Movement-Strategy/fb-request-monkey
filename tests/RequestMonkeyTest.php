<?php

	error_reporting(E_ALL); 
	ini_set( 'display_errors','1');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/php-underscore/underscore.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/fb_request_monkey/fb_request_monkey.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/php_mongorm/php_mongorm.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/functional_test_builder/functional_test_builder.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/libs/fb_sdk/facebook.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/fb_request_monkey/sdk.php');

	class RequestMonkeyTest extends PHPUnit_Framework_TestCase {
		
		static $functionalBuilderConfig;
		
		static $verifyExpectedActual = true;
		
		function __construct() {
			self::$functionalBuilderConfig = self::getFunctionalBuilderConfig();
		} 
		
		public function getFunctionalBuilderConfig() {
			return array(
				'configuration_map' => self::getConfigurationMap(),
				'entry_point_map' => self::getEntryPointMap(),
			);
		}
		
		public function  getActionBuildingFunction() {
			$action = function($query = '', $params = null, $label = null, $method = 'GET', $accessToken = 'test') {
				return RequestMonkeyTest::buildAction($query, $params, $label , $method, $accessToken);
			};
			return $action;
		}
		
		public function buildResponse($responseType, $data, $limit = 10, $count = null, $code = 200, $isSingle = false) {
			$count = $count ? $count : count($data);
			
			$responseMap = array(
				'unpaged_unbatched' => array(
					'get_response' => function($data, $limit) {
						return $data;
					},
				),
				'paged_unbatched' => array(
					'get_response' => function($data, $limit, $isSingle) use($count){
						return array(
							'data' => array(
								$data,
							),
							'count' => $count,
							'limit' => $limit,
							'offset' => 0,
							'include_deleted' => null,
							'paging' => array(
								'next' => 'test',
							),
						);
					},
				),
				'unpaged_batched' => array(
					'get_response' => function($data, $limit, $isSingle, $code) {
							$response = array();
							foreach($data as $dataInBatch) {
								$batchCode = isset($dataInBatch['batch_code']) ? $dataInBatch['batch_code'] : 200;
								$responseInBatch = array(
									'code' => $batchCode,
									'headers' => array(
										'test',
									),
								);
								$responseInBatch['body'] = json_encode($dataInBatch['batch_data']);
								array_push($response, $responseInBatch);
							}
							return $response;
					},
				),
				'paged_batched' => array(
					'get_response' => function($data, $limit, $isSingle, $code) use($count){
							$response = array();
							foreach($data as $dataInBatch) {
								
								$batchCode = isset($dataInBatch['batch_code']) ? $dataInBatch['batch_code'] : 200;
								$responseInBatch = array(
									'code' => $batchCode,
									'headers' => array(
										'test',
									),
								);
								
								$body = array(
									'data' => $dataInBatch['batch_data'],
									'count' => $dataInBatch['batch_count'],
									'limit' => $dataInBatch['batch_limit'],
									'offset' => 0,
									'include_deleted' => null,
									'paging' => array(
										'next' => 'test',
									),
								);
								$responseInBatch['body'] = json_encode($body);
								array_push($response, $responseInBatch);
							}
							return $response;
					},
				),				
				
			);
			
			$response = $responseMap[$responseType]['get_response']($data, $limit, $isSingle, $code);
			
			return $response;
			
		}
		
		public function getResponseBuildingFunction() {
			$response = function($responseType, $data, $limit = 10, $count = null, $isSingle = false) {
				return RequestMonkeyTest::buildResponse($responseType, $data, $limit, $count, $isSingle);
			};
			
			return $response;
		}
		
		public static function buildAction($query = '', $params = null, $label = null, $method = 'GET', $accessToken = 'test') {
			$action = array(
				'query' => $query,
				'method' => $method,
				'access_token' => $accessToken,
			);
			
			if($params) {
				$action['params'] = $params;
			}
			
			if($label) {
				$action['label'] = $action;
			}
			
			return $action;
		}
		
		public function buildExpectedActualArgs($expected, $actual) {
			if($expected != $actual && self::$verifyExpectedActual) {
				$output = Test_Builder::confirmExpected($expected, $actual);
				print_r($output);
			}
			return array(
				 'expected' => $expected,
				 'actual' => $actual,
			);
		}
		
		public function getExpectedActualFunction() {
			$expAct = function($expectedActual) {
				return RequestMonkeyTest::buildExpectedActualArgs($expectedActual['expected'], $expectedActual['actual']);
			};
			
			return $expAct;
		}
		
		public function buildTest($test) {
			Test_Builder::buildTest($test, self::$functionalBuilderConfig);
		}
		
		public function getDefaultConfig() {
			return array(
				'appId' => 1000,
				'secret' => 'abcdef',
				'cookie' => true,
			);
		}
		
		public function getEntryPointMap() {
			
			return array(
				'all' => self::getAllEntryPoint(),
				'action' => self::getActionEntryPoint(),
				'send_many' => self::getSendManyEntryPoint(),
				'send_one' => self::getSendOneEntryPoint(),
				'initialize' => self::getInitializeEntryPoint(),
			);

		}
		
		public function getInitializeEntryPoint() {
			return array(
				'get_output' => function($input, $extraParams) {
					$config = $input['config'];
					FB_Request_Monkey::initialize($config);
					return array(
						'sdk' => FB_Request_Monkey::$sdk,
					);
				},
				'assert_input' => array(),
			);
		}
		
		public function getBaseInitializeConfiguration() {
			return array(
				'get_assert_args' => function($output, $assertInput){
					
					return array(
						'sdk' => $output['sdk'],
						'expected_class' => 'SDK',					
					);

				},
				'asserts' => array (
					'assertNotNull' => array(
						'expected_class',
						'sdk', 
					),
				),
				'input' => array(
					'config' => self::getDefaultConfig(),
				),
			);
		}
		
		public function getAllEntryPoint() {
			
			$expAct = self::getExpectedActualFunction();
			return array(
				'test' => $this,
				'build_input' => function($input) {
					return $input;
				},
				'get_assert_args' => function($output, $assertInput) use($expAct){
					return $expAct(
						array(
							'expected' => $assertInput['expected'],					
							'actual' => $output,
						)
					);
				
				},
				'asserts' => array (
					'assertEquals' => array(
						'expected', 
						'actual',
					),
				),
			);
			
		}
		
		public function getActionEntryPoint() {
			return array(
				'get_output' => function($input, $extraParams) {
					$actions = $input['actions'];
					$failsafeToken = isset($input['failsafe_token']) ? $input['failsafe_token'] : null;
					$callQueue = FB_Request_Monkey::getCallQueue($actions);
					$formattedCallQueue = FB_Request_Monkey::formatCallQueue($callQueue, $failsafeToken);
					return $formattedCallQueue;
				},
			);
		}
				
		public function getSendManyEntryPoint() {
			return array(
				'get_output' => function($input, $extraParams, $test) {
					$actions = $input['actions'];
					$responses = $input['responses'];
					$overflowResponses = isset($input['overflow_responses']) ? $input['overflow_responses'] : array();
					$allResponses = array_merge($responses, $overflowResponses);
					$config = isset($input['config']) ? $input['config'] : RequestMonkeyTest::getDefaultConfig();
					$options = isset($input['options']) ? $input['options'] : array();
					$stubSDK = RequestMonkeyTest::getStubSDK($allResponses, $test);
					FB_Request_Monkey::$sdk = $stubSDK;
					$results = FB_Request_Monkey::sendMany($actions, $config, $options);
					return $results;
				},
			);
		}
		
		public function getSendOneEntryPoint() {
			return array(
				'get_output' => function($input, $extraParams, $test) {
					$actions = $input['actions'];
					$responses = $input['responses'];
					$overflowResponses = isset($input['overflow_responses']) ? $input['overflow_responses'] : array();
					$allResponses = array_merge($responses, $overflowResponses);
					$config = isset($input['config']) ? $input['config'] : RequestMonkeyTest::getDefaultConfig();
					$options = isset($input['options']) ? $input['options'] : array();
					$stubSDK = RequestMonkeyTest::getStubSDK($allResponses, $test);
					FB_Request_Monkey::$sdk = $stubSDK;
					$results = FB_Request_Monkey::sendOne($actions[0], $config, $options);
					return $results;
				},
			);
		}
		
		public function getStubSDK($responses, $test) {
			
			$stubSDK = $test->getMock('SDK');			
								
			$stubSDK->expects($test->any())
				->method('initialize')
				->will($test->returnValue(null));
				
			$stubSDK->expects($test->any())
				->method('transmit')
				->will(call_user_func_array(array($test, "onConsecutiveCalls"), $responses));
			return $stubSDK;
		}	
									
		public function getConfigurationMap() {
			
			$action = self::getActionBuildingFunction();
			$expAct = function($expectedActual) {
				return RequestMonkeyTest::buildExpectedActualArgs($expectedActual['expected'], $expectedActual['actual']);
			};
			
			$response = self::getResponseBuildingFunction();
			
			return array(
				'single_action_single_call' => self::getSingleActionSingleCallConfiguration($action),
				'batch_action_single_call' => self::getBatchActionSingleCallConfiguration($action),

				'unpaged_unbatched_response' => self::getUnpagedUnbatchedResponseConfiguration($action, $response),
			
				'paged_unbatched_response' => self::getPagedUnbatchedResponseConfiguration($action, $response),

				'unpaged_batched_response' => self::getUnpagedBatchedResponseConfiguration($action, $response),
				'paged_batched_response' => self::getPagedBatchedResponseConfiguration($action, $response),
 				'multiple_unpaged_batched_response' => self::getMultipleUnpagedBatchedResponseConfiguration($action, $response),
 				'unpaged_batched_null_response' => self::getUnpagedBatchedNullResponseConfiguration($action, $response),
 				'base_initialize' =>  self::getBaseInitializeConfiguration(),
			);
			
		}

		public function getSingleActionSingleCallConfiguration($action) {

			return array(
				'input' => array(
					'actions' => array(
						$action(
							'me'
						),
					),
				),
				'assert_input' => array(
					'expected' => array(
						array(
							'method' => 'POST',
							'relative_url' => '',
							'params' => array(
								'batch' => array(
									array(
										'method' => 'GET',
										'relative_url' => '/me?access_token=test',
									),
								),
							),
							'actions' => array(
								array(
									'relative_url' => 'me',
									'method' => 'GET',
									'access_token' => 'test',
								),
							),
						),
					),
				),
			);
		}
								
		public function getBatchActionSingleCallConfiguration($action) {
			return array(
				'input' => array(
					'actions' => array(
						$action(
							'me'
						),
						$action(
							'me'
						),
					),
				),
				'assert_input' => array(
					'expected' => array(
						array(
							
							'method' => 'POST',
							'relative_url' => '',
							'params' => array(
								'batch' => array(
									array(
										'method' => 'GET',
										'relative_url' => '/me?access_token=test',
									),
									array(
										'method' => 'GET',
										'relative_url' => '/me?access_token=test',
									),
								),
							),
							'actions' => array(
								array(
									'relative_url' => 'me',
									'method' => 'GET',
									'access_token' => 'test',
								),
								array(
									'relative_url' => 'me',
									'method' => 'GET',
									'access_token' => 'test',
								),
							),
						),
					),
				),
			);
		}
		
		public function getUnpagedUnbatchedResponseConfiguration($action, $response) {
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'unpaged_batched', 
							array(
								array(
									'batch_data' => array(
										'test', 
									),
								),
							)
						),
					),
					'actions' => array(
						$action(
							'me'
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'data' => array(
							array(
								'test',
							),
						),
					),
				),
			);
		}
		
		public function getPagedUnbatchedResponseConfiguration($action, $response) {
						
			$actions = array(
			);
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'paged_batched', 
							array(
								array(
									'batch_data' => array(
										'stuff1',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
							),
							$actions
						),
					),
					'overflow_responses' => array(
						$response(
							'paged_batched', 
							array(
								array(
									'batch_data' => array(
										'stuff2',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
							),
							$actions
						),
					),
					'actions' => array(
						$action(
							'me'
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'data' => array(
							array(
								'stuff1',
							),
							array(
								'stuff2',
							),
						),
					),
				),
			);
			
		}
		
		public function getUnpagedBatchedResponseConfiguration($action, $response) {
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'unpaged_batched', 
							array(
								array(
									'batch_data' => array(
										'test1', 
									),
								),
								array(
									'batch_data' => array(
										'test2', 
									),
								),
							)
						),
					),
					'actions' => array(
						$action(
							'me'
						),
						$action(
							'me'
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'data' => array(
							array(
								'test1',
							),
							array(
								'test2',
							),
						),
					),
				),
			);
		}
		
		public function getUnpagedBatchedNullResponseConfiguration($action, $response) {
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'unpaged_batched', 
							array(
								array(
									'batch_data' => array(
										'test1', 
									),
								),
								array(
									'batch_data' => array(
										'test2', 
									),
								),
							)
						),
					),
					'actions' => array(
						$action(
							'me'
						),
						$action(
							'me'
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
/*
						'data' => array(
							array(
								'test1',
							),
							array(
								'test2',
							),
						),
*/
					),
				),
			);
		}
		
		public function getPagedBatchedResponseConfiguration($action, $response) {
			$actions = array(
			);
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'paged_batched', 
							array(
								array(
									'batch_data' => array(
										'stuff1',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
								array(
									'batch_data' => array(
										'things1',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
							),
							
							$actions
						),
					),
					'overflow_responses' => array(
						$response(
							'paged_batched', 
							array(
								array(
									'batch_data' => array(
										'stuff2',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
								array(
									'batch_data' => array(
										'things2',
									),
									'batch_count' => 2,
									'batch_limit' => 1,
								),
							),
							
							$actions
						),
					),
					'actions' => array(
						$action(
							'me'
						),
						$action(
							'me'
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'data' => array(
							array(
								'stuff1',
							),
							array(
								'things1',
							),
							array(
								'stuff2',
							),
							array(
								'things2',
							),
						),
					),
				),
			);
		}

		public function getMultipleUnpagedBatchedResponseConfiguration($action, $response) {
			
			$actionCount = 53;
			
			$actions = array();
			$expectedData = array();
			$dataForResponses = array();
			$i = 1;
			while($i < $actionCount) {
				$currentData = array(
					'test' . $i,
				);
				$dataForResponse = array(
					'batch_data' => $currentData,
				);
				
				$newAction = $action(
					'me'
				);
				array_push($actions, $newAction);
				array_push($dataForResponses, $dataForResponse);
				array_push($expectedData, $currentData);
				$i++;
				
			}
			
			$chunkedResponses = array_chunk($dataForResponses, 50);
			$chunk1 = $chunkedResponses[0];
			$chunk2 = $chunkedResponses[1];
			
			
			return array(
				'input' => array(
					'responses' => array(
						$response(
							'unpaged_batched', 
							$chunk1
						),
						$response(
							'unpaged_batched', 
							$chunk2
						),
					),
					'actions' => $actions,		
				),
				'assert_input' => array(
					'expected' => array(
						'data' => $expectedData,
					),
				),
			);
		}

		public function testSingleActionSingleCall() {
			
			$test = array(
				'configuration' => 'single_action_single_call',
				'entry_point' => 'action',
			);
			
			return self::buildTest($test);
		}

		/**
	     * @expectedException Exception
	     */		
	    public function testSingleActionSingleCallWithInvalidParams() {
			$test = array(
				'configuration' => 'unpaged_unbatched_response',
				'entry_point' => 'send_many',
				'alterations' => array(
					'input' => function($input) {
						$input['options'] = array(
							'failsafeToken' => 'test',
						);
						$input['actions'][0]['query'] = 'debug_token';
						return $input;
					},
				),
			);
			
			return self::buildTest($test);
		}
		
		public function testSingleActionSingleCallWithEmptyString() {
			$test = array(
				'configuration' => 'single_action_single_call',
				'entry_point' => 'action',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['query'] = '';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'][0]['params']['batch'][0]['relative_url'] = '/?access_token=test';
						$assertInput['expected'][0]['actions'][0]['relative_url'] = '';
						return $assertInput;
					}
				),
			);
			return self::buildTest($test);
		}
		
		public function testBatchActionSingleCallWithActionName() {
			
			$test = array(
				'configuration' => 'batch_action_single_call',
				'entry_point' => 'action',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['name'] = 'test';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'][0]['params']['batch'][0]['name'] = 'test';
						$assertInput['expected'][0]['actions'][0]['name'] = 'test';
						return $assertInput;
					}
				),
			);
							
			return self::buildTest($test);
		}
		
		public function testBatchActionSingleCallWithFailsafeToken() {
			$test = array(
				'configuration' => 'batch_action_single_call',
				'entry_point' => 'action',
				'alterations' => array(
					'input' => function($input) {
						$input['failsafe_token'] = 'test';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'][0]['params']['access_token'] = 'test';
						return $assertInput;
					}
				),
			);
			
			return self::buildTest($test);
		}
				
		public function testSingleActionSingleCallWithBoundaryQuery() {
			
			$test = array(
				'configuration' => 'single_action_single_call',
				'entry_point' => 'action',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['query'] = 'debug_token';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = 	array(
							array(
								'relative_url' => '',
								'method' => 'POST',
								'params' => array(
									'batch' => array(
										array(
											'method' => 'GET',
											'relative_url' => '/debug_token',
										),
									),
								),
								'actions' => array(
									array(
										'relative_url' => 'debug_token',
										'method' => 'GET',
										'access_token' => 'test',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
			
		}
		
		public function testBatchActionSingleCall() {
			
			$test = array(
				'configuration' => 'batch_action_single_call',
				'entry_point' => 'action',
			);
			
			return self::buildTest($test);
			
		}
		
		public function testUnpagedUnbatchedResponse() {
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_unbatched_response',
			);
			
			self::buildTest($test);
		}

		public function testUnpagedUnbatchedResponseWithSingleLabel() {
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = 'test_label';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'test_label' => array(
								array(
									'test',
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		public function testUnpagedUnbatchedResponseWithMultiLabel() {
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = array('label1', 'label2');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'label1' => array(
								'label2' => array(
									array(
										'test',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			$test = self::buildTest($test);
		}
				
		public function testPagedUnbatchedResponse() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_unbatched_response',
			);
			
			self::buildTest($test);
		}
		
		/**
	     * @expectedException Exception
	     */		
	    public function testPagedUnbatchedResponseWithBadCount() {
			$action = self::getActionBuildingFunction();
			$response = self::getResponseBuildingFunction();
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) use($response, $action) {
						$input['responses'] = array(
							$response(
								'paged_unbatched', 
								array(
									'test1',
								),
								
								1, // limit 
								3 // count	
							),
							$response(
								'unpaged_unbatched',
								array()
							),
						);
												
						$input['actions'] = array(
							$action(
								'me'
							),
						);
						
						return $input;
					}
				),
			);
			
			self::buildTest($test);
		}

		public function testPagedUnbatchedResponseWithSingleLabel() {
		
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = 'label1';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'label1' => array(
								array(
									'stuff1',
								),
								array(
									'stuff2',
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
			
		}
				
		public function testPagedUnbatchedResponseWithMultiLabel() {
		
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = array('type1', 'label1');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'type1' => array(
								'label1' => array(
									array(
										'stuff1',
									),
									array(
										'stuff2',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
			
		}
				
		public function testUnpagedBatchedResponse() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_batched_response',
			);
			
			self::buildTest($test);
		}
		
		/**
	     * @expectedException Exception
	     */		
		public function testUnpagedBatchedResponseError() {
			
			$response = self::getResponseBuildingFunction();
			$action = self::getActionBuildingFunction();
			$test = array(
				'configuration' => 'unpaged_batched_response',
				'entry_point' => 'send_many',
				'alterations' => array(
					'input' => function($input) use($response, $action) {
						$input['responses'] = array(
							$response(
								'unpaged_batched', 
								array(
									array(
										'batch_data' => array(
											'test1', 
										),
									),
									array(
										'batch_data' => array(
											'test1', 
										),
										'batch_code' => 299,
									),
								)
							),
						);
						
						$input['actions'] = array(
							$action(
								'me'
							),
							$action(
								'me'
							),
						);
						
						return $input;
					}
				),
			);
			
			self::buildTest($test);
		}
		
		public function testUnPagedBatchedResponseWithSingleLabel() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = 'label1';
						$input['actions'][1]['label'] = 'label2';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'label1' => array(
								array(
									'test1',
								),
							),
							'label2' => array(
								array(
									'test2',
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		public function testUnpagedBatchedResponseWithMultiLabel() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = array('type1', 'label1');
						$input['actions'][1]['label'] = array('type1', 'label2');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'type1' => array(
								'label1' => array(
									array(
										'test1',
									),
								),
								'label2' => array(
									array(
										'test2',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		public function testUnpagedBatchedResponseErrorWithAllowErrors() {
			
			$response = self::getResponseBuildingFunction();
			$action = self::getActionBuildingFunction();
			$test = array(
				'configuration' => 'unpaged_batched_response',
				'entry_point' => 'send_many',
				'alterations' => array(
					'input' => function($input) use($response, $action) {
						$input['responses'] = array(
							$response(
								'unpaged_batched', 
								array(
									array(
										'batch_data' => array(
											'test1', 
										),
									),
									array(
										'batch_data' => array(
											'error' => array(
												'message' => 'test',
												'type' => 'test',
												'code' => 299,
											),
										),
										'batch_code' => 299,
									),
								)
								
							),
						);
						
						$input['options']['allowErrors'] = true;
						$input['actions'] = array(
							$action(
								'me'
							),
							$action(
								'me'
							),
						);
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['data'] = array(
							array(
								'test1',
							),
							array(
								'error' => array(
									'message' => 'test',
									'type' => 'test',
									'code' => 299,
								),
							),
						);
						
						return $assertInput;
					}
				),
			);
			
			self::buildTest($test);
			
		}
		
		public function testPagedBatchedResponse() {
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_batched_response',
			);
			
			self::buildTest($test);
		}

		public function testPagedBatchedResponseWithSingleLabel() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = 'stuff';
						$input['actions'][1]['label'] = 'things';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'stuff' => array(
								
								array(
									'stuff1',
								),
								array(
									'stuff2',
								),
							),
							'things' => array(
								array(
									'things1',
								),
								array(
									'things2',
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		public function testPagedBatchedResponseWithMultiLabel() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'paged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['actions'][0]['label'] = array('stuff_and_things', 'stuff');
						$input['actions'][1]['label'] = array('stuff_and_things', 'things');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected'] = array(
							'stuff_and_things' => array(
								'stuff' => array(
									
									array(
										'stuff1',
									),
									array(
										'stuff2',
									),
								),
								'things' => array(
									array(
										'things1',
									),
									array(
										'things2',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		/**
	     * @expectedException Exception
	     */		
		public function testPagedBatchedResponseErrorWithBadCount() {
			
			$response = self::getResponseBuildingFunction();
			$action = self::getActionBuildingFunction();
			$test = array(
				'configuration' => 'paged_batched_response',
				'entry_point' => 'send_many',
				'alterations' => array(
					'input' => function($input) use($response, $action) {
						$input['overflow_responses'] = array(
							$response(
								'paged_batched', 
								array(
									array(
										'batch_data' => array(
											'stuff2',
										),
										'batch_count' => 2,
										'batch_limit' => 1,
									),
								)
							),
						);
						
						$input['actions'] = array(
							$action(
								'me'
							),
							$action(
								'me'
							),
						);
												
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['data'] = array(
							array(
								'test1',
							),
							array(
								'error' => array(
									'message' => 'test',
									'type' => 'test',
									'code' => 299,
								),
							),
						);
						
						return $assertInput;
					}
				),
			);
			
			self::buildTest($test);
			
		}
		
		public function testMultipleUnpagedBatchedResponses() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'multiple_unpaged_batched_response',
			);
			
			self::buildTest($test);
		}
		
		public function testSendOne() {
			$test = array(
				'entry_point' => 'send_one',
				'configuration' => 'unpaged_unbatched_response',
			);
			
			self::buildTest($test);
		}
		
		public function testInitializeWithNullSDK() {
			$test = array(
				'configuration' => 'base_initialize',
				'entry_point' => 'initialize',
				'alterations' => array(
					'get_output' => function($getOutput) {
						$newGetOutput = function($input, $extraParams) {
							$config = $input['config'];
							FB_Request_Monkey::$sdk = null;
							FB_Request_Monkey::initialize($config);
							return array(
								'sdk' => FB_Request_Monkey::$sdk,
							);
						};
						return $newGetOutput;
					},
				),
			);
			
			return self::buildTest($test);
		}
		
		public function testInitialize() {
			
			$test = array(
				'configuration' => 'base_initialize',
				'entry_point' => 'initialize',
			);
			
			return self::buildTest($test);
			
		}
		
/*
		public function testUnpagedBatchedNullResponse() {
			
			$test = array(
				'entry_point' => 'send_many',
				'configuration' => 'unpaged_batched_null_response',
			);
			
			self::buildTest($test);
		}
*/
		
		
	}
