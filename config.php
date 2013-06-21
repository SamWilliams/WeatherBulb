<?php

//******** REQUIRED *******

//forecast.io api key. register here: http://developer.forecast.io
$apiKey = "<Your api key goes here>";

//Url for the api of hue bridge. http://developers.meethue.com/gettingstarted.html
//should look something like "http://0.0.0.0/api/newdeveloper" ... with a differen't IP of course.
$hubApiUrl = "http://<ip address here>/api/<hue bridge account name here>";

//longitude and latitude of bulbs in decimal degrees.
//Example for SOHO NYC:
  //$longitude = '-74.000833';
  //$latitude = '40.723056';

$longitude = '<longitude here>';
$latitude = '<latitude here>';



//********* OPTIONAL **********
//forecast x minutes ahead. Note: if the forecast.io does not provide minutely forecasts for the given location
//the script defeaults to the one hour forecast.
$forecastTime = 10;

