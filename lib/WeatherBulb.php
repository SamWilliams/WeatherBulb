<?php
/**
 * Helper Class for forecast.io webservice
 */

class WeatherBulb{
  
  private $_forecastApiKey;
  private $_hueApiUrl = FALSE;
  private $_cacheResponse = TRUE;
  private $_longitude;
  private $_latitude;
  private $_errors = Array();
  
  private $_colors = Array("hot"=>
                  Array("hue"=>15, "saturation"=>100),
                "rain"=>
                  Array("hue"=>284, "saturation"=>100),
                "cloudy"=>
                  Array("hue"=>72, "saturation"=>80,),
                "clear-night"=>
                  Array("hue"=>236, "saturation"=>80),
                "partly-cloudy-night"=>
                  Array("hue"=>236, "saturation"=>80),
                "wind"=>
                  Array("hue"=>89, "saturation"=>100),
                "clear-day"=>
                  Array("hue"=>89, "saturation"=>100),
                "partly-cloudy-day"=>
                  Array("hue"=>72, "saturation"=>100),
                "default"=>
                  Array("hue"=>115, "saturation"=>100),
                );
  private $_forecastApiUrl = 'https://api.forecast.io/forecast/';
  
  //used for unit testing.
  private $_hueApiResponse = FALSE;
  private $_forecastIoApiResponse = FALSE;

  
  /**
   * Create a new instance
   * 
   * @param String $apiKey
   */
  function __construct($config) {
    $this->_apiKey = $config['apiKey'];
    $this->_hueApiUrl = $config['hueApiUrl'];
    $this->_longitude = $config['longitude'];
    $this->_latitude = $config['latitude'];
    if(isset($config['cacheResponse'])){
      $this->_cacheResponse = $config['cacheResponse'];
    }
  }

  public function getErrors(){
     return $this->_errors;
  }

  private function _getMinuteForecast($forecastMin){
    $forecastMin = (int) $forecastMin;
    if($forecastMin < 1 || $forecastMin > 59){
      $errors[] = "forecast minute is out of range.";
      return FALSE;
    }
    $forecastMin = $forecastMin - 1; 

    $forecast = new stdClass();

    $results = $this->_forecastIoApiCall($this->_longitude, $this->_latitude);
    if($results == FALSE){
      $errors[] = "Unable to get data from forecast.io.";
      return FALSE;
    }

    if(!isset($results->minutely) || !isset($results->minutely->data)){
      $data = $results->hourly->data;
      $forecast->minute = $data[0];
    }else{
      $data = $results->minutely->data;
      $forecast->minute = $data[$forecastMin];
    }

    $forecast->temp = $results->currently->temperature;
    $forecast->icon = $results->minutely->icon;

    if($this->_cacheResponse){ //save forecast to file for later use.
      $fp = fopen('/tmp/weather.json', 'w');
      fwrite($fp, json_encode($results));
      fclose($fp);
    }

    return $forecast;
  }
  
  /*
   * convert forecast.io response to standard HSV format
   */
  private function _forecastToHsv($forecast){
    //if it's going to rain enough.
    if(isset($forecast->minute->probability) && $forecast->minute->probability > .2
                                             && $forecast->minute->precipIntensity > .002){
      $hsv->h = $this->_colors['rain']['hue'];
      $hsv->s = $this->_colors['rain']['saturation'];
      $hsv->v = $this->_precipToValue($forecast->minute->precipIntensity);
    }elseif($forecast->temp >= 88){ //if it's hot enough
      $hsv->h = $this->_colors['hot']['hue'];
      $hsv->s = $this->_colors['hot']['saturation'];
      $hsv->v = $this->_tempToValue($forecast->temp);
    }else{ //else set to icon color and brightness set to temp
      if(isset($this->_colors[$forecast->icon])){
        $hsv->h = $this->_colors[$forecast->icon]['hue'];
        $hsv->s = $this->_colors[$forecast->icon]['saturation'];
        $hsv->v = $this->_tempToValue($forecast->temp);
      }else{//no matching entry found for the forecast.io icon.
        $hsv->h = $this->_colors['unknown']['hue'];
        $hsv->s = $this->_colors['unknown']['saturation'];
        $hsv->v = $this->_colors['unknown'][20];
      }
    }
    return $hsv;
  }

  /*
   * convert temp to value (hsv)
   */
  private function _tempToValue($temp){
    return $temp * 0.50;
  }

  /*
   * convert precipitation in inches to value (hsv)
   */
  private function _precipToValue($intensity){
    if($intensity <= 0.002){
      $intensity = 0;
    }elseif($intensity > 0.002 && $intensity <= 0.017){
      $intensity = 10;
    }elseif($intensity > 0.017 && $intensity <= 0.1){
      $intensity = 30;
    }elseif($intensity > 0.1 && $intensity <= 0.3){
      $intensity = 50;
    }elseif($intensity > 0.3){
      $intensity = 75;
    }
    return $intensity;
  }

  /*
   * convert standard hsv color values as an object to hsb for the hue light.
   *
   */
  private function _hsvToHsb($hsv){

    $hsb = new stdClass();
    $sat = floor(($hsv->s/100.0) * 255.0);
    $hsb->s = (int) $sat;

    $hue = floor(($hsv->h/360.0) * 65535.0);
    $hsb->h = (int) $hue;

    $bri = floor(($hsv->v/100.0) * 255.0);
    $hsb->b = (int) $bri;
    if($hsb->b == 0){
      $hsb->b = 1;
    }
    return $hsb;
  }

  /*
   * Change a hue lightbulbs color based off of the forecast from forecast.io
   * 
   * If there's a > 20% chance of rain, set purple and set intensity to inches of rain.
   * If not rain temp > 88 degrees, override colors and set intensity to temp.
   * Everything else, set intensity to temp
   */
  public function updateBulb($lightId, $forecastTime){

    $forecast = $this->_getMinuteForecast($forecastTime);

    $hsv = $this->_forecastToHsv($forecast);

    $hueHsb = $this->_hsvToHsb($hsv);

    $data = array(
        "hue" => $hueHsb->h
        ,"bri" => $hueHsb->b
        ,"sat" => $hueHsb->s
        ,"on" => true
        );
    if(!$this->_hueApiCall($lightId,"state",$data)){
      return FALSE;
    }
    return TRUE;
  }


  private function _forecastIoApiCall($latitude, $longitude){
    if($this->_forecastIoApiResponse !== FALSE){
      return $this->_forecastIoApiResponse;
    }

    $url = $this->_forecastApiUrl . "/" .
        $this->_apiKey . '/' .
        $latitude . ',' . $longitude .
        '?units=auto';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    if (isset($response) && $response != '') {
      return json_decode($content);
    } else {
      return false;
    }
  } 

  private function _hueApiCall($lightId,$method,$data){
    if($this->_hueApiResponse !== FALSE){
      return $this->_hueApiResponse;
    }
    if($this->_hueApiUrl == ""){
      $this->_errors[] = "No api url is set for the hue hub.";
      return FALSE;
    }

    $jsonData = json_encode($data);
    $url = $this->_hueApiUrl . "/lights/" . $lightId . "/$method/";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
    $response = curl_exec($ch);
    $results = json_decode($response);
    return $results;
  }

  //Used for unit testing.
  public function setForecastIoApiResponse($response){
    $this->_forecastIoApiResponse = $response;
  }
  
  //Used for unit testing.
  public function setHueApiResponse($response){
    $this->_hueApiResponse = $response;
  }
}

?>
