<?php
require_once "PHPUnit/Extensions/Database/TestCase.php";
require_once (__DIR__ . "/../lib/WeatherBulb.php");
DEFINE("UNIT_TEST",TRUE);

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class WeatherBulbTest extends PHPUnit_Framework_TestCase 
{
  private $config;

  function testUpdateBulbValid() {
    $WB = new WeatherBulb($this->_config);
    
    
    $forecastResponse = json_decode(file_get_contents("forecast.io.json"));
    $WB->setForecastIoApiResponse($forecastResponse);
    $hueResponse = Array("success"=>"/lights/2/state");
    //$WB->setHueApiResponse($hueResponse);
    $lightId = 2;
    $forecastTime = 10;
    $results = $WB->updateBulb($lightId, $forecastTime);
    $this->assertTrue($results,"update success"); 
    return;
  }
 
    function setUp() {
    error_reporting(E_ALL);
    $this->_config = Array("apiKey"=>"95652491924c70c5262604b12cd8483e",
    "hueApiUrl"=>"http://10.3.52.112/api/newdeveloper",
    "longitude"=>"-74.00",
    "latitude"=>"40.72"
    );

    parent::setUp();
  }
  
  function tearDown() {
    parent::tearDown();
  }
}
