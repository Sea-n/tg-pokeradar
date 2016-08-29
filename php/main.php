<?php
require('config.php');

$url = 'https://www.pokeradar.io/api/v1/submissions?' . http_build_query(RANGE_LATLNG);
$json = file_get_contents($url);
$data = json_decode($json, true);
$data = $data['data'];

foreach ($data as $item) {
	if (in_array($item['pokemonId'], SCAN) &&
		((time() - $item['created']) < 600) &&
		($item['userId'] == '13661365')) {
		$addr = getAddr($item['latitude'], $item['longitude']);
		$name = NAME[$item['pokemonId']];
		getTelegram('sendVenue', array(
			'chat_id' => CHANNEL,
			'title' => $name . ', ' . date('h:i:s', $item['created']+900),
			'latitude' => $item['latitude'],
			'longitude' => $item['longitude'],
			'address' => $addr
		));
	}
}

function getAddr(string $lat, string $lon): string {
	if (count(GOOGLE_API_KEY) == 0) {
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lon}&language=zh-TW");
	} else {
		$key = GOOGLE_API_KEY[rand(0, count(GOOGLE_API_KEY)-1)];
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lon}&language=zh-TW&key={$key}");
	}
	$data = json_decode($json, true);
	$addr = $data['results'][0]['formatted_address'] ?? 'Unknown';
	return $addr;
}

function getTelegram(string $method, array $query) {
	$botToken = BOT_TOKEN;
	$url = "https://api.telegram.org/bot{$botToken}/{$method}";
	$query = json_encode($query);
	file_get_contents($url, false, stream_context_create(array(
		'http' => array(
			'method'  => 'POST',
			'content' => $query,
			'header'  => array(
				'Content-Type: application/json; charset=utf-8'
			)
		)
	)));
}
