<?php
require_once(__DIR__ . '/libs/WeatherBulb.php');
require_once(__DIR__ . '/config.php');


//requirements:
//hue bulbs
//hue hub ip address and developer name
//bulb ids
//forecast.io api key
//log/lat
//curl

$config = Array("apiKey"=>$apiKey,
  "hubIp"=>$hubIp,
  "bulbs"=>$bulbIds,
  "logitude"=>$longitude,
  "latitude"=>$latitude,
  "colors"=>$colors);


$WB = new WeatherBulb($config);
$WB->updateBulb($forecastTime);

