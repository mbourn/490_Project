<?php
function start(){
  // Change these
  define('API_KEY',      '75p5ptqc5060jj'                          );
  define('API_SECRET',   'GJIsoTietQCigBWb'                        );

  // You must pre-register your redirect_uri at https://www.linkedin.com/secure/developer
  define('REDIRECT_URI', 'https://mbourn.com/authorized/authorized.php');
  define('SCOPE',        'r_fullprofile r_emailaddress r_network');

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
//        echo "<p><b>Call Token</b></p>";
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
//        echo "<p><b>Call Auth</b></p>";
      }
  }
 
  // Congratulations! You have a valid token. Now fetch your profile 
//echo "<p>fetching user</p>";
  $user = fetch('GET', '/v1/people/~');

  // Get the user's network
//echo "<p>fetchin network</p>";
  $network = fetch('GET', '/v1/people/~/connections:(first-name,last-name,site-standard-profile-request)');

//  print "Hello $user->firstName $user->lastName.";

  // Add the user to the Users table, return the user's primary key
//echo "<p>call load_user</p>";
  $last_id = load_user($user);

  // Add the user's network to the Network table with the user's primary
  // key as the foreign key.
//echo "<p>call load_network</p>";
  $errors = load_network($network, $last_id);
  var_dump($last_id);
  var_dump($errors);
  $return_info = array(
    'last_id' => $last_id,
    'errors' => $errors,
  );
  return $return_info;
}

function getAuthorizationCode() {
//echo "<p><b>Get Auth</b></p>";
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
//echo "<p><b>Get Token</b></p>";
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
//echo "<p>Fetching</p>";
//  print $_SESSION['access_token'];
 
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

// This function takes the user php object returned from LinkedIn and adds
// the relevent values to the database 
function load_user($user){ 
//  echo "<p><b>Loading user</b></p>";
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";
  
  // Create the connection 
  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
//    echo "Connected successfully ...<br>";
  }
  
  // Load the contents of the passed PHP object into the database
  $fname = $user->firstName;
  $lname = $user->lastName;
  $sql = "INSERT INTO Users (f_name, l_name) VALUES('$fname', '$lname')";

  if( $conn->query($sql) === TRUE){
//  	echo "<p>You've been added to the database</p>";
  }else{
  	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }

  $last_id = $conn->insert_id;
  mysqli_close($conn);
  return $last_id;
}

// This function takes the network PHP object returned by the LinkedIn
// server and adds the relevent values to the database.  It also takes an
// integer that is used to add the foreign key that references the user
// whose contacts these are.
function load_network( $network, $last_id ){
//  echo "<p><b>Loading the network into the database ...</b></p>";
  // Create the database connection
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
//    echo "Connected successfully ...<br>";
  }                
  $errors=0;
  // Add a row to the Network table for each contact in the network object
  for( $i=0; $i<count($network->values); $i++){ 
    $fname = $network->values[$i]->firstName;
    $lname = $network->values[$i]->lastName;
    $url = $network->values[$i]->siteStandardProfileRequest->url;
    $sql = "INSERT INTO Network (c_of, f_name, l_name, l_url) VALUES('$last_id', '$fname', '$lname', '$url')";

// Diagnostics    
//echo "<p>First: " . $fname . " Last: " . $lname . " URLL " . $url . "</p>";

    if( $conn->query($sql) === TRUE){
    }else{
      $errors+=1;
    }
  }
  mysqli_close($conn);
  return $errors;
}

function load_single_contact($lname, $fname){

}

// Count the number of contacts that have set their profiles to "private" and
// return that number
function count_privates($last_id){
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";
  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
  }

  $sql = "SELECT f_name FROM Network WHERE f_name = 'private' AND c_of = $last_id";     
  echo $sql;
  $count=0;
  $result = mysqli_query($conn, $sql);
  if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
      $count+=1;
    }
  }
  mysqli_close($conn);
  return $count;
}

function count_private_returns($network){

}

function count_query_errors($sql_output){

}

function edit_individ_contact($contact){

}

function clear_database(){

}


function delete_db(){
  echo "Deleting";
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

  // Delete all rows from the Network table
  $sql="DELETE FROM Network";
  if($conn->query($sql)===TRUE){
    echo"<p><b>Network deleted</b></p>";
  }else{
    echo "<p><b>Error deleting record: " . $conn->error . "</b></p>";
  }

  // Reset the primary key to 0
  $sql="ALTER TABLE Network AUTO_INCREMENT=1";
  if($conn->query($sql)===TRUE){
    echo"<p><b>Network PK reset</b></p>";
  }else{
    echo "<p><b>Error Altering Network's PK: " . $conn->error . "</b></p>";
  }

  // Delete all rows from the Users table
  $sql="DELETE FROM Users";
  if($conn->query($sql)===TRUE){
    echo"<p><b>Users deleted</b></p>";
  }else{
    echo "<p><b>Error deleting record: " . $conn->error . "</b></p>";
  }
  
  // Reset the primary key to 0
  $sql="ALTER TABLE Users AUTO_INCREMENT=1";
  if($conn->query($sql)===TRUE){
    echo"<p><b>Users PK reset</b></p>";
  }else{
    echo "<p><b>Error Altering Users' PK: " . $conn->error . "</b></p>";
  }
}
