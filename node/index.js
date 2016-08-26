var request = require('request');
var qs = require('querystring');
var fs = require('fs');

var config = JSON.parse(fs.readFileSync(__dirname + '/config.json', 'utf8'));

request("https://www.pokeradar.io/api/v1/submissions?" + qs.stringify(config.latlng), function (error, respone, body) {
	data = JSON.parse(body);
	data = data.data;
	for (var i = 0, len = data.length; i < len; i++) {
		var item = data[i];
		if ((config.scan.indexOf(item.pokemonId) != -1) &&
			((Math.floor(Date.now() / 1000) - item.created) < 600) &&
			(item.trainerName == '(Poke Radar Prediction)')) {
			request('https://maps.googleapis.com/maps/api/geocode/json?' + qs.stringify({
				latlng: item.latitude + "," + item.longitude,
				language: "zh-TW"
			}), function (error, respone, body) {
				var maps = JSON.parse(body);
				var addr = maps.results[0].formatted_address;
				request('https://api.telegram.org/bot' + config.botToken + '/sendVenue?' + qs.stringify({
					chat_id: config.channel,
					title: config.name[item.pokemonId],
					latitude: item.latitude,
					longitude: item.longitude,
					address: addr
				}), function (error, respone, body) {
				});
			});
		}
	}
});
