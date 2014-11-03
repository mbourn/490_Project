<?php
// Change these
define('API_KEY',      '75p5ptqc5060jj'                          );
define('API_SECRET',   'GJIsoTietQCigBWb'                        );

// You must pre-register your redirect_uri at https://www.linkedin.com/secure/developer
define('REDIRECT_URI', 'https://mbourn.com/authorized/authorized.php');
define('SCOPE',        'r_fullprofile r_emailaddress r_network'                              );


//echo "<p><b>" $_SERVER['SERVER_NAME'] . " " . $_SERVER['SCRIPT_NAME'] . "</b></p>";

// You'll probably use a database
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
        getAccessToken();
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
        getAuthorizationCode();
    }
}
 
// Congratulations! You have a valid token. Now fetch your profile 
$user = fetch('GET', '/v1/people/~');

//$network = fetch('GET', '/v1/people/~/connections');
$network = fetch('GET', '/v1/people/~/connections:(first-name,last-name,site-standard-profile-request)');


print "Hello $user->firstName $user->lastName.";
echo "<br>";
echo $network->values[0]->firstName;


load_user($user);
load_network($network);
exit;
 
function getAuthorizationCode() {
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
    $params = array(
        'grant_type' => 'authorization_code',
        'client_id' => API_KEY,
        'client_secret' => API_SECRET,
        'code' => $_GET['code'],
        'redirect_uri' => REDIRECT_URI,
    );
     
    // Access Token request
    $url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
     
    // Tell streams to make a POST request
    $context = stream_context_create(
        array('http' => 
            array('method' => 'POST',
            )
        )
    );
 
    // Retrieve access token information
    $response = file_get_contents($url, false, $context);
 
    // Native PHP object, please
    $token = json_decode($response);
 
    // Store access token and expiration time
    $_SESSION['access_token'] = $token->access_token; // guard this! 
    $_SESSION['expires_in']   = $token->expires_in; // relative time (in seconds)
    $_SESSION['expires_at']   = time() + $_SESSION['expires_in']; // absolute time
     
    return true;
}
 
function fetch($method, $resource, $body = '') {
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
 
    // Native PHP object, please
    return json_decode($response);
}


function load_user($user){ 
  echo "<p><b>Loading user</b></p>";
  var_dump($user);
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";
  
  // Create the connection 
  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
    echo "Connected successfully ...<br>";
  }
  
  // Load the contents of the passed PHP object into the database
  echo "Create vars...<br>";
  $fname = $user->firstName;
  $lname = $user->lastName;
  $sql = "INSERT INTO Users (f_name, l_name) VALUES('$fname', '$lname')";

echo "<p><b>Fname: " . $fname . " Lname: " . $lname . "<br>Query: " . $sql . "</b></p>";

  if( $conn->query($sql) === TRUE){
  	echo "<p>You've been added to the database</p>";
  }else{
  	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }
  mysqli_close($conn);

}


function load_network( $network ){


echo "<p><b>Loading the your network into the database ...</b></p>";
//var_dump($network);
//$fname = $network->values[0]->siteStandardProfileRequest->url;
//echo $fname;

  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
    echo "Connected successfully ...<br>";
  }                



    for( $i=0; $i<count($network->values); $i++){ 
    $fname = $network->values[$i]->firstName;
    $lname = $network->values[$i]->lastName;
    $url = $network->values[$i]->siteStandardProfileRequest->url;
    $sql = "INSERT INTO Network (f_name, l_name, l_url) VALUES('$fname', '$lname', '$url')";

echo "<p>First: " . $fname . " Last: " . $lname . " URLL " . $url . "</p>";

    if( $conn->query($sql) === TRUE){
      echo "<p>Your network has been added to the database</p>";
    }else{
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  }
  mysqli_close($conn);
}

