# Wargaming Public API
Basic PHP library handling Wargaming Public API. Uses namespace to get data sources so it is compatible with all sources (WoT, Blitz, WGN, WoWp and also WoWs) and all servers (EU,NA,ASIA,RU,KR). All it needs is application_id that can be obtained here https://eu.wargaming.net/developers/applications/ (for EU).

##Sample usage##
``` php
<?php

// Include API
require_once 'api.php';

// API Instance where demo is your application_id
$api = new Wargaming\API('demo');

// Test how it works
try {
	$data = $api->get('wgn/clans/list',array('search'=>'PSQD'));
	
	// Display info about WoT Clan PSQD
	var_dump($data);
	
} catch (Exception $e) {

	die($e->getMessage());
	
}
```
