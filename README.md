WeatherBulb 0.5
===============
Change the color of your Philips Hue bulb based on the current minute weather forecast from the forecast.io api

If You are tired of getting soaked because you forgot to check one of the 500+ weather apps you have on your phone, and happen to have a Philips Hue kit, this script is for you.


Requirements
-------------------------
* A Philips Hue lightbulb and bridge (http://meethue.com)
* Developer access and ip address of your Hue bridge (how-to here http://developers.meethue.com/gettingstarted.html)
* forecast.io api key (free registration at https://developer.forecast.io/register)
* Longitude/Latitude coordinates, in decimal degrees, for your location. 

Setup
-------------------------
Edit the config.php file with the appropriate settings.

  * **apiKey**: The api key provided at https://developer.forecast.io/  after registering for a free account.

  * **hubApiUrl**: this is the full url to access the api, according to the steps http://developers.meethue.com/gettingstarted.html here. The url should include the name of the user you created on the hue bridge for accessing the api.

  * **longitude/latitude**: In decimal degrees format (40.723056 -74.000833). Note: forecast.io may not provide "minutely" forecasts for all locations. The script should default to the forecast for the next hour if it can't find a minute forecast.

You'll also want to create a cronjob to run `weatherBulbCron.php` every 2 minutes or so to stay under the current free forecast.io api limit.
  Below is an example one that I use.

`*/2 * * * * /usr/local/bin/php /www/weatherBulbCron.php > /tmp/hue.txt`


Usage
-------------------------
* **look into the light**
  * If it's purple, It's going to rain, grab a rain coat or umbrella. The brighter it is, the more it will rain.
  * If it's red, It's going to be hot and you might want to put on some sunscreen.
  * If it's orange, all is good, with clear/overcast day skies. Brightness is based on temperature. 
  * If it's blue, it's night time, but all is still good with clear/overcast night skies. Brightness is based on temperature.





