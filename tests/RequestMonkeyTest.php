<?php

	error_reporting(E_ALL); 
	ini_set( 'display_errors','1');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/php-underscore/underscore.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/fb_request_monkey/fb_request_monkey.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/php_mongorm/php_mongorm.php');
	require_once('/Users/cbranch101/Sites/clay/movement_strategy/functional_test_builder/functional_test_builder.php');

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
		
		public function buildResponse($responseType, $data, $actions, $limit = 10, $count = null, $code = 200, $isSingle = false) {
			$count = $count ? $count : count($data);
			
			$responseMap = array(
				'unpaged_unbatched' => array(
					'get_response' => function($data, $limit) {
						return $data;
					},
					'is_batched' => false, 
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
					'is_batched' => false,
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
					'is_batched' => true,
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
					'is_batched' => true,
				),				
				
			);
			
			$response = array();
			$response['response'] = $responseMap[$responseType]['get_response']($data, $limit, $isSingle, $code);
			$response['isBatched'] = $responseMap[$responseType]['is_batched'];
			$response['actions'] = $actions;
			
			return $response;
			
		}
		
		public function getResponseBuildingFunction() {
			$response = function($responseType, $data, $actions, $limit = 10, $count = null, $isSingle = false) {
				return RequestMonkeyTest::buildResponse($responseType, $data, $actions, $limit, $count, $isSingle);
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
				
		public function getEntryPointMap() {
			
			return array(
				'all' => self::getAllEntryPoint(),
				'action' => self::getActionEntryPoint(),
				'response' => self::getResponseEntryPoint(),
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
		
		public function getResponseEntryPoint() {
			return array(
				'get_output' => function($input, $extraParams) {
					
					$responseQueue = $input['response_queue'];
					$overflowResponseQueue = isset($input['overflow_response_queue']) ? $input['overflow_response_queue'] : array();
					$results = array();
					$allowErrors = isset($input['allow_errors']) ? $input['allow_errors'] : false;
					$actionCount = 0;
					$processedResponses = FB_Request_Monkey::processResponseQueue($responseQueue, $actionCount, $allowErrors);
					$results = FB_Request_Monkey::addDataFromProcessedResponsesToResults($processedResponses, $results);
					$overflowActions = FB_Request_Monkey::getOverflowActions($processedResponses);
					
					if(count($overflowActions) > 0) {
						
						$overflowProcessedResponses = FB_Request_Monkey::processResponseQueue($overflowResponseQueue, $actionCount, $allowErrors);
						
						
						
						// because these are overflow requests, the sent result number is inaccurate, so it is set to zero
						// to correct for the discrepency
						$overflowProcessedResponses = FB_Request_Monkey::setSentDataCountToZero($overflowProcessedResponses);
						
						$results = FB_Request_Monkey::addDataFromProcessedResponsesToResults($overflowProcessedResponses, $results);
						
						// combine the two response sets together so they can be checked
						// for the number of results
						$processedResponses = array_merge($processedResponses, $overflowProcessedResponses);
						
					}
					
					FB_Request_Monkey::checkDataCount($processedResponses, $allowErrors);
					
					return array(
						'results' => $results,
						'overflow_actions' => $overflowActions,
					);
				},
			);
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
							'relative_url' => '/me',
							'method' => 'GET',
							'params' => array(
								'access_token' => 'test',
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
			$actions = array(
				$action(
					'me'
				),
			);
			return array(
				'input' => array(
					'response_queue' => array(
						$response(
							'unpaged_unbatched', 
							array(
								'test1' => 'test',
							),
							$actions
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'results' => array(
							'data' => array(
								array(
									'test1' => 'test',
								),
							),
						),
						'overflow_actions' => array(),
					),
				),
			);
		}
		
		public function getPagedUnbatchedResponseConfiguration($action, $response) {
			
			$actions = array(
				$action(
					'me'
				),
			);
			
			return array(
				'input' => array(
					'response_queue' => array(
						$response(
							'paged_unbatched', 
							array(
								'test1',
							),
							
							$actions,
							1, // limit 
							2 // count	
						),
					),
					'overflow_response_queue' => array(
						$response(
							'paged_unbatched', 
							array(
								'test2',
							),
							
							$actions,
							1, // limit
							2 // count	
						),
					),
								
				),
				'assert_input' => array(
					'expected' => array(
						'results' => array(
							'data' => array(
								array(
									array(
										'test1',
									),
								),
								array(
									array(
										'test2',
									),
								),
							),
						),
						'overflow_actions' => array(
							array(
								'query' => 'me',
								'method' => 'GET',
								'access_token' => 'test',
								'params' => array(
									'offset' => 1,
								),
							),
						),
					),
				),
			);
		}
		
		public function getUnpagedBatchedResponseConfiguration($action, $response) {
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			return array(
				'input' => array(
					'response_queue' => array(
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
							),
							
							$actions
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'results' => array(
							'data' => array(
								array(
									'test1',
								),
								array(
									'test2',
								),
							),
						),
						'overflow_actions' => array(
						
						),
					),
				),
			);
		}
		
		public function getPagedBatchedResponseConfiguration($action, $response) {
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			return array(
				'input' => array(
					'response_queue' => array(
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
					'overflow_response_queue' => array(
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
				),
				'assert_input' => array(
					'expected' => array(
						'results' => array(
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
						'overflow_actions' => array(
							array(
								'query' => 'me',
								'method' => 'GET',
								'access_token' => 'test',
								'params' => array(
									'offset' => 1,
								),
							),
							array(
								'query' => 'me',
								'method' => 'GET',
								'access_token' => 'test',
								'params' => array(
									'offset' => 1,
								),
							),
						),		
					),
				),
			);
		}
		
		public function getMultipleUnpagedBatchedResponseConfiguration($action, $response) {
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			return array(
				'input' => array(
					'response_queue' => array(
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
							),
							
							$actions
						),
						$response(
							'unpaged_batched', 
							array(
								array(
									'batch_data' => array(
										'test3',
									),
								),
								array(
									'batch_data' => array(
										'test4',
									),
								),
							),
							
							$actions
						),
					),			
				),
				'assert_input' => array(
					'expected' => array(
						'results' => array(
							'data' => array(
								array(
									'test1',
								),
								array(
									'test2',
								),
								array(
									'test3',
								),
								array(
									'test4',
								),
							),
						),
						'overflow_actions' => array(
						),
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
								'relative_url' => '/debug_token',
								'method' => 'GET',
								'params' => array(),
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
				'entry_point' => 'response',
				'configuration' => 'unpaged_unbatched_response',
			);
			
			self::buildTest($test);
		}
		
		public function testUnpagedUnbatchedResponseWithSingleLabel() {
			
			$test = array(
				'entry_point' => 'response',
				'configuration' => 'unpaged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = 'test';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
							'test' => array(
								array(
									'test1' => 'test',
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
				'entry_point' => 'response',
				'configuration' => 'unpaged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = array('label1', 'label2');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
							'label1' => array(
								'label2' => array(
									array(
										'test1' => 'test',
									),
								),
							),
						);
						return $assertInput;
					},
				),
			);
		}
				
		public function testPagedUnbatchedResponse() {
			
			$test = array(
				'entry_point' => 'response',
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
			
			$actions = array(
				$action(
					'me'
				),
			);
			
			$test = array(
				'entry_point' => 'response',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) use($response, $actions) {
						$input['response_queue'] = array(
							$response(
								'paged_unbatched', 
								array(
									'test1',
								),
								
								$actions,
								1, // limit 
								3 // count	
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
				'entry_point' => 'response',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = 'label1';
						$input['overflow_response_queue'][0]['actions'][0]['label'] = 'label1';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
							'label1' => array(
								array(
									array(
										'test1',
									),
								),
								array(
									array(
										'test2',
									),
								),
							),
						);
						$assertInput['expected']['overflow_actions'][0]['label'] = 'label1';
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
			
		}
		
		public function testPagedUnbatchedResponseWithMultiLabel() {
		
			$test = array(
				'entry_point' => 'response',
				'configuration' => 'paged_unbatched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = array('type1', 'label1');
						$input['overflow_response_queue'][0]['actions'][0]['label'] = array('type1', 'label1');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
							'type1' => array(
								'label1' => array(
									array(
										array(
											'test1',
										),
									),
									array(
										array(
											'test2',
										),
									),
								),
							),
						);
						$assertInput['expected']['overflow_actions'][0]['label'] = array('type1', 'label1');
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
			
		}
				
		public function testUnpagedBatchedResponse() {
			
			$test = array(
				'entry_point' => 'response',
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
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			$test = array(
				'configuration' => 'unpaged_batched_response',
				'entry_point' => 'response',
				'alterations' => array(
					'input' => function($input) use($response, $actions) {
						$input['response_queue'] = array(
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
								),
								
								$actions
								
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
				'entry_point' => 'response',
				'configuration' => 'unpaged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = 'label1';
						$input['response_queue'][0]['actions'][1]['label'] = 'label2';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
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
				'entry_point' => 'response',
				'configuration' => 'unpaged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = array('type1', 'label1');
						$input['response_queue'][0]['actions'][1]['label'] = array('type1', 'label2');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
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
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			$test = array(
				'configuration' => 'unpaged_batched_response',
				'entry_point' => 'response',
				'alterations' => array(
					'input' => function($input) use($response, $actions) {
						$input['response_queue'] = array(
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
								),
								
								$actions
								
							),
						);
						
						$input['allow_errors'] = true;
						
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results']['data'] = array(
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
				'entry_point' => 'response',
				'configuration' => 'paged_batched_response',
			);
			
			self::buildTest($test);
		}
		
		public function testPagedBatchedResponseWithSingleLabel() {
			
			$test = array(
				'entry_point' => 'response',
				'configuration' => 'paged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = 'stuff';
						$input['response_queue'][0]['actions'][1]['label'] = 'things';
						$input['overflow_response_queue'][0]['actions'][0]['label'] = 'stuff';
						$input['overflow_response_queue'][0]['actions'][1]['label'] = 'things';
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
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
						$assertInput['expected']['overflow_actions'][0]['label'] = 'stuff';
						$assertInput['expected']['overflow_actions'][1]['label'] = 'things';
						return $assertInput;
					},
				),
			);
			
			self::buildTest($test);
		}
		
		public function testPagedBatchedResponseWithMultiLabel() {
			
			$test = array(
				'entry_point' => 'response',
				'configuration' => 'paged_batched_response',
				'alterations' => array(
					'input' => function($input) {
						$input['response_queue'][0]['actions'][0]['label'] = array('stuff_and_things', 'stuff');
						$input['response_queue'][0]['actions'][1]['label'] = array('stuff_and_things', 'things');
						$input['overflow_response_queue'][0]['actions'][0]['label'] = array('stuff_and_things', 'stuff');
						$input['overflow_response_queue'][0]['actions'][1]['label'] = array('stuff_and_things', 'things');
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results'] = array(
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
						$assertInput['expected']['overflow_actions'][0]['label'] = array('stuff_and_things', 'stuff');
						$assertInput['expected']['overflow_actions'][1]['label'] = array('stuff_and_things', 'things');
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
			$actions = array(
				$action(
					'me'
				),
				$action(
					'me'
				),
			);
			$test = array(
				'configuration' => 'paged_batched_response',
				'entry_point' => 'response',
				'alterations' => array(
					'input' => function($input) use($response, $actions) {
						$input['overflow_response_queue'] = array(
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
						);
												
						return $input;
					},
					'assert_input' => function($assertInput) {
						$assertInput['expected']['results']['data'] = array(
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
				'entry_point' => 'response',
				'configuration' => 'multiple_unpaged_batched_response',
			);
			
			self::buildTest($test);
		}
		
		
				
		
	}
