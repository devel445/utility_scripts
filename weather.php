<?php
require_once('vendor/autoload.php');

// Uses Open weather map API
define('OWM_APPID', '<ENTER WEATHER API KEY HERE>');
define('CACHE_TIME', 90); // in seconds
date_default_timezone_set('Asia/Kolkata');

$response = new PowernLib\Response();
$query = (isset($_GET['q'])) ? trim($_GET['q']) : '';

if (empty($query)) {
	$response->setCode(400);
	$response->setData("No search query given");
	$response->send();
	die();
}

function check_cache_validity($redisVal) {
	$now = new DateTime();
	$diff = $now->getTimestamp() - $redisVal->dt;
	if ($diff > CACHE_TIME) {
		return false;
	}

	return true;
}

function get_weather($q) {
	$headers = array('Accept' => 'application/json');
	$options = array();
	$url = "http://api.openweathermap.org/data/2.5/weather?q=" . $q . "&APPID=" . OWM_APPID;
	$request = Requests::get($url, $headers, $options);

	return array($request->status_code, $request->body);
}

// escape quotes
$query = addslashes($query);

$redisClient = new Predis\Client();
$redisKey = "weatherq_" . $query;
$redisVal = $redisClient->get($redisKey);
$cache_valid = false;

if (!is_null($redisVal)) {
	$redisVal = json_decode($redisVal);
	$cache_valid = check_cache_validity($redisVal);

	if ($cache_valid) {
		$response->setCode(200);
		$response->setData($redisVal);
		$response->setJsonResponse();
		$response->send();

		die();
	}
}

// no value in cache or it was time invalidated
list($code, $weather_api_response) = get_weather($query);
$weather = json_decode($weather_api_response);

$response->setCode($weather->cod);
$response->setData($weather);
$response->setJsonResponse();
$response->send();

if ($weather->cod === 200) {
	// write in redis
	$redisClient->set($redisKey, $weather_api_response);
}

die();
