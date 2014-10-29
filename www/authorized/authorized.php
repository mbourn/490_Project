<?php include '../cgi-bin/linkedin_auth.php';?>
<!DOCTYPE html>
<html>
<head></head>
<body>
 <p>
<a href="https://apilinkedin.com/v1/people/~?oauth2_access_token=AQXYCYhofl1BZnlKO9nk2NDiJo6RLXNE-jBIAflv8heoXwtqX_271-ULtMWWd4qNIuQ-fisoCjCm56HBRruVeVj6MsdL-hzl9lcaU0ggBqgOcuk-jtVTgl28eDhO0KBiwInw9XhBu-vNxgOmmbX1sU7dnBv1etutwZDstfYpmLVuqhQhbwQ">Do it the hard way</a>
</p>
</body>
</html>


<?php 
/*function fetch($method, $resource, $body = '') {
echo "<h3>Fetching</h3><br>";
    print $_SESSION['access_token'];
    echo "<br>";
    print $_SESSION['expires_in'];

    $headers = array(
        'Authorization' => 'Bearer ' . $_SESSION['access_token'],
        'x-li-format' => 'json', // Comment out to use XML
    );
echo "<br><br>" . $headers . "<br><br>"; 
    $params = array(
//      'param1' => 'value1',
    );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource;
 
    // Append query parameters (if there are any)
    if (count($params)) { $url .= '?' . http_build_query($params); } 
 
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    // And use OAuth 2 access token as Authorization
    $context = stream_context_create(
        array('http' => 
            array('method' => $method,
                  'header' => $headers,
            )
        )
    );
 
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
 
    // Native PHP object, please
if ( $response ){
echo "<br><br>The server responded with: " . json_decode($response) . "<br>";
}else{
  echo "<br><br>Something went wrong.<br><br>";
}
    return json_decode($response);
}*/
?>

