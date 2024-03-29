# Wargaming Public API
Basic PHP library handling Wargaming Public API. Uses namespace to get data sources so it is compatible with all sources (WoT, Blitz, WGN, WoWp and also WoWs) and all servers (EU,NA,ASIA,RU,KR). All it needs is application_id that can be obtained here https://eu.wargaming.net/developers/applications/ (for EU).

## Requirements
- PHP 7.0+
- CURL

## Sample usage
``` php
<?php

use Wargaming\Language\EN as EnglishLanguage;
use Wargaming\Server\EU as EuropeanServer;

require_once __DIR__.'/vendor/autoload.php';

// API Instance where YOUR_APPLICATION_ID is your application_id registered for the server you use.
$lang = new EnglishLanguage();
$server = new EuropeanServer('YOUR_APPLICATION_ID');
$api = new Wargaming\API($lang, $server);

// Test how it works
try {
	$data = $api->get('wgn/clans/list', ['search'=>'PSQD','fields'=>'name,tag,clan_id']);
	
	// Display info about WoT Clan PSQD
	var_dump($data);
	
} catch (Exception $e) {

	die($e->getMessage());
	
}
```

## Sample ETag usage
``` php
<?php

use Wargaming\Language\EN as EnglishLanguage;
use Wargaming\Server\EU as EuropeanServer;

require_once __DIR__.'/vendor/autoload.php';

// API Instance where YOUR_APPLICATION_ID is your application_id registered for the server you use.
$lang = new EnglishLanguage();
$server = new EuropeanServer('YOUR_APPLICATION_ID');
$api = new Wargaming\API($lang, $server);

// Test how it works
try {
	// As a 4th param provide ETag. If tag of a clan S3AL wasn't changed method will return true. If it changed new data will be returned.
	$info = $api->get('wgn/clans/info', ['clan_id'=>'500034335','fields'=>'tag'], false, '813ac115749538da9b3b61fd4069fd44');
	
	var_dump($info);die;
	
} catch( Exception $e) {
	
	exit('Error: '.$e->getMessage());
	
}
```

## How to get ETag from API request
``` php
<?php

use Wargaming\Language\EN as EnglishLanguage;
use Wargaming\Server\EU as EuropeanServer;

require_once __DIR__.'/vendor/autoload.php';

// API Instance where YOUR_APPLICATION_ID is your application_id registered for the server you use.
$lang = new EnglishLanguage();
$server = new EuropeanServer('YOUR_APPLICATION_ID');
$api = new Wargaming\API($lang, $server);

// Test how it works
try {
	// Set 5th param to boolean TRUE. That way method will return array with following format: ['headers'=>[],'data'=>StdClass]
	$info = $api->get('wgn/clans/info', ['clan_id'=>'500034335','fields'=>'tag'], false, null, true);

	// Get response headers. Remember to store ETag without quotes cause $api->get() method add those when ETag is provided.
	var_dump($info['headers']['ETag']);die;
	
} catch( Exception $e) {
	
	exit('Error: '.$e->getMessage());
	
}
```

## News
### 1.4.1 - 2022-05-09
- Allow chaining in set methods.
- Simplified some of the conditions.
- Fixed few typos in documentation.

### 1.4.0 - 2021-06-08
- Prepared for use in dependency injection.
- Added a `public function setSSLVerification(bool $state)` method to change SSL connection verification status (CURLOPT_SSL_VERIFYPEER).
- Changed api instance declaration to allow multiple servers
- Added server instance declaration to allow injected application id (api key)

### 1.3.1 - 2018-02-26
Creating composer package.

### 1.3 - 2017-10-09
Moving to PHP7, changing code to PSR.

### 1.2 - 2016-02-06
From now if CURL return an error class will throw exception with that error message. Also added support for ETag ([Documentation](https://eu.wargaming.net/developers/documentation/guide/getting-started/#etag)) and HTTP headers.

### 1.1 - 2015-08-20
Moved to CURL for requests. Speed boost shoold be from 50% - 150% cause of reusing connection. Checked on my WN8 calculation class. Regular calculation takes 0.32s

### 1.04 - 2015-08-19
Fixed few error messages.

### 1.03 - 2015-08-17
Fixed error handling for associative arrays, added new error messages.

### 1.02 - 2015-08-17
Added new parameter to `API::get()` called $assoc. If this parameter is set to true, function will return associative array instead of object/array of objects.
