<?php 
// Fill the keys and secrets you retrieved after registering your app
$oauth = new OAuth("75p5ptqc5060jj", "GJIsoTietQCigBWb");
$oauth->setToken("d7a9bd69-440f-8519-25e897c83452", "82c5b3f9-b59a-49ca-bbd5-fa3a2cb9ff1d");
 
$params = array();
$headers = array();
$method = OAUTH_HTTP_METHOD_GET;
  
// Specify LinkedIn API endpoint to retrieve your own profile
$url = "https://api.linkedin.com/v1/people/~";
 
// By default, the LinkedIn API responses are in XML format. If you prefer JSON, simply specify the format in your call
// $url = "https://api.linkedin.com/v1/people/~?format=json";
 
// Make call to LinkedIn to retrieve your own profile
$oauth->fetch($url, $params, $method, $headers);
echo 'Test';  
echo $oauth->getLastResponse();
?>
