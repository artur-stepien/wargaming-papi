<?php

/**
 * @package     Wargaming.API
 * @version     1.02
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
			$url = 'https://'.$this->server.'/'.$namespace.'/?application_id='.$this->application_id.'&language_id='.$this->language.'&'.http_build_query($options);
			
			$buff = file_get_contents($url);
			
			$response = json_decode($buff, $assoc);
			
			// Servers return correct data
			if( is_object($response) AND $response->status=='ok' ) {

				return $response->data;
				
			// Api server return error
			} elseif( is_object($response) AND $response->status=='error' ) {
				
				// Format data
				throw new \Exception($this->translateError($response->error->message), $response->error->code);
				
			// Page not found
			} else {
				
				throw new \Exception('You set wrong server or namespace.', 404);
				
			}

		}
		
		/**
		 * Returns human readable error message.
		 * 
		 * @param   string   $error   The error to translate.
		 * 
		 * @return string
		 */
		protected function translateError($error) {
			$messages = array(
			   'METHOD_NOT_FOUND' => 'Invalid API method.',
			   'METHOD_DISABLED' => 'Specified method is disabled.',
			   'APPLICATION_IS_BLOCKED' => 'Application is blocked by the administration.',
			   'INVALID_APPLICATION_ID' => 'Invalid application_id.',
			   'INVALID_IP_ADDRESS' => 'Invalid IP-address for the server application.',
			   'REQUEST_LIMIT_EXCEEDED' => 'Request limit is exceeded.',
			   'SOURCE_NOT_AVAILABLE' => 'Data source is not available.',
			);
					 
			if( isset($messages[$error]) ) {
				return $messages[$error];
			} elseif( stripos($error, '_NOT_SPECIFIED') ) {
				return 'Required field '.strtolower(str_ireplace('_NOT_SPECIFIED', '', $error)).' is not specified.';
			} elseif( stripos($error, '_NOT_FOUND') ) {
				return 'Data for field '.strtolower(str_ireplace('_NOT_FOUND', '', $error)).' not found.';
			} elseif( stripos($error, '_LIST_LIMIT_EXCEEDED') ) {
				return 'Limit of passed-in identifiers in the '.strtolower(str_ireplace('_LIST_LIMIT_EXCEEDED', '', $error)).' exceeded.';
			} elseif( stripos($error, 'INVALID_') ) {
				return 'Specified field value '.strtolower(str_ireplace('INVALID_', '', $error)).' is not valid.';
			} else {
				return $error;
			}
		}
		
	}
	
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

	CONST SERVER_EU = 'api.worldoftanks.eu';
	CONST SERVER_NA = 'api.worldoftanks.com';
	CONST SERVER_RU = 'api.worldoftanks.ru';
	CONST SERVER_ASIA = 'api.worldoftanks.asia';
	CONST SERVER_KR = 'api.worldoftanks.kr';
	
}