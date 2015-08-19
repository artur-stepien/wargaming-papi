<?php

/**
 * @package     Wargaming.API
 * @version     1.04
 * @author      Artur Stępień (artur.stepien@bestproject.pl)
 * @copyright   Copyright (C) 2015 Artur Stępień, All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Wargaming {
	
	class API {
		protected $application_id;
		protected $language;
		protected $server;

		/**
		 * Create Wargaming API instance
		 * 
		 * @param   string   $application_id   Application ID obtainable from Wargaming websites
		 * @param   string   $language         Language of data (mostly errors). Default: LANGUAGE_ENGLISH
		 * @param   string   $server           Server/Cluster that should be used as source. DefaultL SERVER_EU
		 */
		public function __construct($application_id, $language = LANGUAGE_ENGLISH, $server = SERVER_EU) {
			$this->application_id = $application_id;
			$this->language = $language;
			$this->server = $server;
		}

		/**
		 * Return data from Wargaming servers. Documentation for all API methods can be found here: https://eu.wargaming.net/developers/api_reference
		 * 
		 * @param   string   $namespace   Namespace of data you want to get(for example wgn/servers/info or wot/account/list )
		 * @param   array    $options     All the options required for this field to work except application_id and language (for example array('fields'=>'server','game'=>'wot'))
		 * @param   float    $assoc       If set to true function will return associative array instead of object/array of objects.
		 */
		public function get($namespace, Array $options = array(), $assoc = false) {
			
			// Build query url
			$url = 'https://'.$this->server.'/'.$namespace.'/?application_id='.$this->application_id.'&language='.$this->language.'&'.http_build_query($options);

			// Get response
			$buff = file_get_contents($url);
			
			// Wrong response (probably wrong server URL)
			if( $buff === false ) {
				throw new \Exception('Wrong server or namespace.', 404);
			} else {
				
				// Convert response to object or array depending on $assoc param
				$response = json_decode($buff, $assoc);

				// User chose object format
				if( is_object($response) ) {

					// Servers return correct data
					if( $response->status=='ok') {

						return $response->data;

					// Api server return error
					} elseif( $response->status=='error' ) {

						// Create exception
						throw new \Exception( $this->translateError($response->error->message, $namespace), $response->error->code );

					// Page not found
					} else {

						throw new \Exception('You set wrong server or namespace.', 404);

					}

				// User chose array format
				} elseif( is_array($response) ) {

					// Servers return correct data
					if( $response['status']=='ok') {

						return $response['data'];

					// Api server return error
					} elseif( $response['status']=='error' ) {

						// Create exception
						throw new \Exception( $this->translateError($response['error']['message'], $namespace), $response['error']['code'] );

					// Page not found
					} else {

						throw new \Exception('You set wrong server or namespace.', 404);

					}

				// Unsupported response format
				} else  {

					throw new \Exception('Wrong response format.', 502);

				}
			}

			return false;
		}
		
		/**
		 * Returns human readable error message.
		 * 
		 * @param   string   $error       The error to translate.
		 * @param   string   $namespace   Namespace passed into get() function.
		 * 
		 * @return string
		 */
		protected function translateError($error, $namespace = null) {
			
			$messages = array(
			   'SEARCH_NOT_SPECIFIED' => 'Parameter <b>search</b> not specified.',
			   'NOT_ENOUGH_SEARCH_LENGTH' => '<b>Search</b> parameter is not long enough. Minimum length: 3 characters.',
			   'ACCOUNT_ID_LIST_LIMIT_EXCEEDED' => 'Limit of passed-in <b>account_id</b> IDs exceeded. Maximum: 100.',
			   'METHOD_NOT_FOUND' => 'Invalid API method <b>'.$namespace.'</b>.',
			   'METHOD_DISABLED' => 'Specified method is disabled.',
			   'APPLICATION_IS_BLOCKED' => 'Application is blocked by the administration.',
			   'INVALID_APPLICATION_ID' => 'Invalid <b>application_id</b>.',
			   'INVALID_IP_ADDRESS' => 'Invalid IP-address for the server application.',
			   'REQUEST_LIMIT_EXCEEDED' => 'Request limit is exceeded.',
			   'SOURCE_NOT_AVAILABLE' => 'Data source is not available.',
			   'INVALID_FIELDS' => 'Invalid fields specified in <b>fields</b> parameter.',
			   'AUTH_CANCEL' => 'Application authorization cancelled by user.',
			   'AUTH_EXPIRED' => 'User authorization timed out.',
			   'AUTH_ERROR' => 'Authentication error.',
			   'MEMBER_ID_LIST_LIMIT_EXCEEDED' => 'Limit of passed-in <b>member_id</b> IDs exceeded. Maximum: 100.',
			   'CLAN_ID_LIST_LIMIT_EXCEEDED' => 'Limit of passed-in <b>clan_id</b> IDs exceeded. Maximum: 100.',
			   'INCOMPATIBLE_MODULE_IDS' => 'Specified modules are incompatible in a single configuration.',
			   'ACCOUNT_ID_NOT_SPECIFIED' => 'Required parameter <b>account_id</b> was not specified.',
			   'TYPE_NOT_SPECIFIED' => 'Required parameter <b>type</b> was not specified.',
			   'INVALID_TYPE' => 'Invalid value set in <b>type</b> parameter.',
			   'RATINGS_NOT_FOUND' => 'No rating details for specified date.',
			   'RANK_FIELD_NOT_SPECIFIED' => 'Required parameter <b>rank_field</b> not specified.',
			   'INVALID_RANK_FIELD' => 'Invalid value set in <b>rank_field</b> parameter.',
			   'INVALID_CLAN_ID' => 'Invalid value set in <b>clan_id</b> parameter. Clan with that ID probably don\'t exist.',
			   'CLAN_ID_NOT_SPECIFIED' => 'Required parameter <b>clan_id</b> was not specified.',
			   'INVALID_LIMIT' => 'Invalid value set in <b>limit</b> parameter.',
			);
					 
			if( isset($messages[$error]) ) {
				return $messages[$error];
			} elseif( stripos($error, '_NOT_SPECIFIED') ) {
				return 'Required field <b>'.strtolower(str_ireplace('_NOT_SPECIFIED', '', $error)).'</b> is not specified.';
			} elseif( stripos($error, '_NOT_FOUND') ) {
				return 'Data for <b>'.strtolower(str_ireplace('_NOT_FOUND', '', $error)).'</b> not found.';
			} elseif( stripos($error, '_LIST_LIMIT_EXCEEDED') ) {
				return 'Limit of passed-in identifiers in the <b>'.strtolower(str_ireplace('_LIST_LIMIT_EXCEEDED', '', $error)).'</b> exceeded.';
			} elseif( stripos($error, 'INVALID_') ) {
				return 'Specified field value <b>'.strtolower(str_ireplace('INVALID_', '', $error)).'</b> is not valid.';
			} else {
				return $error;
			}
		}
		
	}
	
	// Supported language codes (used mostly in Tankpedia queries)
	CONST LANGUAGE_ENGLISH = 'en';
	CONST LANGUAGE_POLISH = 'pl';
	CONST LANGUAGE_RUSSIAN = 'ru';
	CONST LANGUAGE_DEUTSCH = 'de';
	CONST LANGUAGE_FRENCH = 'fr';
	CONST LANGUAGE_SPANISH = 'es';
	CONST LANGUAGE_CHINESE = 'zh-cn';
	CONST LANGUAGE_TURKISH = 'tr';
	CONST LANGUAGE_CZECH = 'cs';
	CONST LANGUAGE_THAI = 'th';
	CONST LANGUAGE_VIETNAMESE = 'vi';
	
	// Supported servers
	CONST SERVER_EU = 'api.worldoftanks.eu';
	CONST SERVER_NA = 'api.worldoftanks.com';
	CONST SERVER_RU = 'api.worldoftanks.ru';
	CONST SERVER_ASIA = 'api.worldoftanks.asia';
	CONST SERVER_KR = 'api.worldoftanks.kr';
	
}