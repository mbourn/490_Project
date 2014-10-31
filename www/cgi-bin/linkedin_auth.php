<?php
// Change these
define('API_KEY',      '75p5ptqc5060jj'                                           );
define('API_SECRET',   'GJIsoTietQCigBWb'                                         );

// You must pre-register your redirect_uri at https://www.linkedin.com/secure/developer
define('REDIRECT_URI', 'https://mbourn.com/authorized/authorized.php'             );
define('SCOPE',        'r_basicprofile r_emailaddress r_network'                  );
 
// You'll probably use a database
session_destroy();
$_SESSION = array();
setcookie("linkedin", "");
session_name('linkedin');
session_start();
 
// OAuth 2 Control Flow
if (isset($_GET['error'])) {
    // LinkedIn returned an error
    print $_GET['error'] . ': ' . $_GET['error_description'];
    exit;
} elseif (isset($_GET['code'])) {
    // User authorized your application
    if ($_SESSION['state'] == $_GET['state']) {
        // Get token so you can make API calls
      echo "<p>Have Authorization, getting token.</p>";
      echo "<p><b>Auth code is:</b> " . $_GET['code'] . "</p>";
      if (getAccessToken()){
        echo "<p>getAccessToken() returned true</p>";
      }else{
        echo "<p>getAccessToken() returned false</p>";
      }
    } else {
        // CSRF attack? Or did you mix up your states?
        exit;
    }
} else { 
    if ((empty($_SESSION['expires_at'])) || (time() > $_SESSION['expires_at'])) {
        // Token has expired, clear the state
        $_SESSION = array();
    }
    if (empty($_SESSION['access_token'])) {
      // Start authorization process
      echo "<p>First step, getting Authorization</p><br>";
        getAuthorizationCode();
    }
}
 
// Congratulations! You have a valid token. Now fetch your profile 
echo "<p><b>Calling Fetch</b></p>";
$user = fetch('GET', '/v1/people/~:(firstName, lastName)');
$fname = $user->firstName;
$lname = $user->lastName;
echo "<p><b>Hello" . $fname . " " . $lname .". </b></p>";
exit;
 
function getAuthorizationCode() {
echo "<p>Getting Auth Code</p>";
    $params = array(
        'response_type' => 'code',
        'client_id' => API_KEY,
        'scope' => SCOPE,
        'state' => uniqid('', true), // unique long string
        'redirect_uri' => REDIRECT_URI,
    );
 
    // Authentication request
    $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
     
    // Needed to identify request when it returns to us
    $_SESSION['state'] = $params['state'];
 
    // Redirect user to authenticate
    header("Location: $url");
    exit;
}
     
function getAccessToken() {
echo "<p><b>Getting Access Token</b></p>";
    $params = array(
        'grant_type' => 'authorization_code',
        'client_id' => API_KEY,
        'client_secret' => API_SECRET,
        'code' => $_GET['code'],
        'redirect_uri' => REDIRECT_URI,
    );
     
    // Access Token request
    $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
echo "<p>URL is: " . $url . "</p>";


    // Tell streams to make a POST request
    $context = stream_context_create(
        array('http' => 
            array('method' => 'POST',
            )
        )
    );
 
    // Retrieve access token information
    $response = file_get_contents($url, false, $context);
    echo "<p><b>The response is:</b> " . $response . "</p>"; 
    // Native PHP object, please
    $token = json_decode($response);
 
    // Store access token and expiration time
    $_SESSION['access_token'] = $token->access_token; // guard this! 
    $_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
    $_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time
     
    return true;
}

function fetch($method, $resource, $body = '') {
echo "<p><b>Fetching Profile</b></p>";
    print $_SESSION['access_token'];
 
    $opts = array(
        'http'=>array(
            'method' => $method,
            'header' => "Authorization: Bearer " . $_SESSION['access_token'] . "\r\n" . "x-li-format: json\r\n"
        )
    );
 
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource;
 
    // Append query parameters (if there are any)
    if (count($params)) { $url .= '?' . http_build_query($params); }
 
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    // And use OAuth 2 access token as Authorization
    $context = stream_context_create($opts);
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);

if ( $response === false ){
  echo "<p>Something went wrong, file_get_content() returned FALSE</p>";
}else{
  print $response;

    // Native PHP object, please
    return json_decode($response);
}
}

/* 
function fetch($method, $resource, $body = '') {
echo "<p><b>Fetching</b></p>";
    print $_SESSION['access_token'];
    echo "<br>";
    print $_SESSION['expires_in'];

    $headers = "Authorization: Bearer " . $_SESSION["access_token"] . "\r\n" . "x-li-format: json\r\n"; // Comment out to use XML

echo "<p><b>The headers are: </b></p>"; 
print_r( $headers );
echo "<br>";

    $params = array(
    // 'param1' => 'value1',
    );
     
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource;
echo "<p><b>URL: " . $url . "</b></p>"; 

    // Append query parameters (if there are any)
    if (count($params)) { $url .= '?' . http_build_query($params); } 
 
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    // And use OAuth 2 access token as Authorization
/*    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"Authorization: Bearer " . $_SESSION["access_token"] . "\r\n" . "x-liformat: json\r\n"));

    $context = stream_context_create(
      array("http"=>
        array("method" => "GET",
          "header" => $headers,
        )
      )
    );

 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
$str_resp = ($response) ? 'true' : 'false';
echo $str_resp; 
echo gettype( $response );
echo count( $response );
    // Native PHP object, please
if ( $response === false ){
  echo "<p><b>Something went wrong, file_get_contents() returned FALSE.</b></p>";
error_reporting(E_ALL | E_STRICT);
$errLvl = error_reporting(); 
for ($i = 0; $i < 15;  $i++ ) { 
    print FriendlyErrorType($errLvl & pow(2, $i)) . "<br>\\n"; 
} 

function FriendlyErrorType($type) 
{ 
    switch($type) 
    { 
        case E_ERROR: // 1 // 
            return 'E_ERROR'; 
        case E_WARNING: // 2 // 
            return 'E_WARNING'; 
        case E_PARSE: // 4 // 
            return 'E_PARSE'; 
        case E_NOTICE: // 8 // 
            return 'E_NOTICE'; 
        case E_CORE_ERROR: // 16 // 
            return 'E_CORE_ERROR'; 
        case E_CORE_WARNING: // 32 // 
            return 'E_CORE_WARNING'; 
        case E_CORE_ERROR: // 64 // 
            return 'E_COMPILE_ERROR'; 
        case E_CORE_WARNING: // 128 // 
            return 'E_COMPILE_WARNING'; 
        case E_USER_ERROR: // 256 // 
            return 'E_USER_ERROR'; 
        case E_USER_WARNING: // 512 // 
            return 'E_USER_WARNING'; 
        case E_USER_NOTICE: // 1024 // 
            return 'E_USER_NOTICE'; 
        case E_STRICT: // 2048 // 
            return 'E_STRICT'; 
        case E_RECOVERABLE_ERROR: // 4096 // 
            return 'E_RECOVERABLE_ERROR'; 
        case E_DEPRECATED: // 8192 // 
            return 'E_DEPRECATED'; 
        case E_USER_DEPRECATED: // 16384 // 
            return 'E_USER_DEPRECATED'; 
    } 
    return ""; 
} 
}else{
echo "<p><b>The server responded with:</b> " . json_decode($response) . "</p>";
}
    return json_decode($response);
}*/
