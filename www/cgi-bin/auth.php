
<?php
function start(){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
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
  error_reporting(E_ALL);
  ini_set('display_errors','On');
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
  error_reporting(E_ALL);
  ini_set('display_errors','On');
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
  error_reporting(E_ALL);
  ini_set('display_errors','On');
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
    //if (count($params)) { $url .= '?' . http_build_query($params); }
 
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
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  
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
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  // Create the database connection
  $conn = create_db_connection();
  // Add a row to the Network table for each contact in the network object
  $errors=0;
  for( $i=0; $i<count($network->values); $i++){ 
    $fname = $network->values[$i]->firstName;
    $lname = $network->values[$i]->lastName;
    $url = $network->values[$i]->siteStandardProfileRequest->url;
    $fname = strtr($fname, ',', " ");
    $lname = strtr($lname, ',', " ");
    $sql = "INSERT INTO Network (c_of, f_name, l_name, l_url) VALUES('$last_id', '$fname', '$lname', '$url')";
    if( $conn->query($sql) === TRUE){
    }else{
      $errors+=1;
    }
  }
  mysqli_close($conn);
  return $errors;
}
// Count the number of contacts that have set their profiles to "private" 
// and return that number
function count_privates($last_id){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  $sql = "SELECT f_name FROM Network WHERE f_name = 'private' AND c_of = $last_id";     
//  echo $sql;
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


function make_one($c_id){
  $conn = create_db_connection();
  $sql = "SELECT f_name, l_name, l_url FROM Network WHERE c_id = $c_id";
  $result = mysqli_query($conn, $sql);
  $res = mysqli_fetch_array($result);
  $f_name = $res['f_name'];
  $l_name = $res['l_name'];
  $l_rul = $res['l_url'];
  $vcar_content = make_vcard_content($f_name, $l_name, $l_url);
var_dump($vcard_content);
  $vcard = make_vcard($vcard_content, $f_name, $l_name);
  dl_card($vcard);
  
  /*
  //parameterize the input from user, defend against SQL Injection
  $stmt = $db->prepare('update people set name = ? where id = ?');
  $stmt->bind_param('si',$name,$id);
  $stmt->execute();*/
}

function edit_individ_contact($contact){

}

function delete_db(){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  echo "Deleting";
  // Create the connection
  $conn = create_db_connection();
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
function create_db_connection(){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
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
  return $conn;
}


function create_all($last_id){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  
  // Create and submit query
  $sql="SELECT f_name, l_name, l_url FROM Network WHERE c_of = $last_id";
  $result = mysqli_query($conn, $sql);
   
  // Create zip file to hold all the vCards
  $zip = new ZipArchive();
  $zip_file = 'vCards/linkedin_contacts.zip';
  if( $zip->open($zip_file, ZipArchive::CREATE)!==TRUE){
    exit("Cannot open <$zip_file>\n");
  }
  //var_dump($return);
  /*  
  while($row = mysqli_fetch_array($result)){
    $id=$row['c_id'];
    $of=$row['c_of'];
    $f_name=$row['f_name'];
    $l_name=$row['l_name'];
    $l_url=$row['l_url'];
    $email=$row['email'];
    $addr=$row['addr'];
    $phone1=$row['phone1'];
    $phone2=$row['phone2'];
    $twitr=$row['twitr'];
    $vcard_content = "BEGIN:VCARD\r";
    $vcard_content .= "VERSION:3.0\r";
    $vcard_content .= "N:".$l_name.";".$f_name.";;\r"; 
    $vcard_content .= "item2.URL;tpe=pref:".$l_url."\r";
    $vcard_content .= 'item2.X-ABLabel:_$!<LinkedInPage>!$_\r';
    $vcard_content .= "X-ABShowAs:COMPANY\r";
    $vcard_content .= "END:VCARD";
    echo "<pre><p><b>".$vcard_content."</b></p></pre>";
  }*/

  while($row = mysqli_fetch_array($result)){
    // var_dump($row);
    $f_name=$row['f_name'];
    $l_name=$row['l_name'];
    $l_url=$row['l_url'];
    $vcard_content = make_vcard_content($f_name, $l_name, $l_url);
    $vcard = make_vcard($vcard_content, $f_name, $l_name);
    $zip->addFile($vcard);
  }

  // Finish the zip file and force download it to the user
  $zip->close();
  dl_card($zip_file);

  // Delete the files
  delete_files();
  exit;
}

// Delete all of the files in the vCards directory
function delete_files(){
  $files = glob('vCards/*');
  foreach($files as $file){
    if(is_file($file)){
      unlink($file);
    }   
  }
}


function make_vcard_content($f_name, $l_name, $l_url){
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  $vcard_content = "BEGIN:VCARD\r";
  $vcard_content .= "VERSION:3.0\r";
  $vcard_content .= "N:".$l_name.";".$f_name.";;\r";
  $vcard_content .= "item1.URL;tpe=pref:".$l_url."\r";
  $vcard_content .= 'item1.X-ABLabel:_$!<LinkedInPage>!$_\r';
  $vcard_content .= "X-ABShowAs:COMPANY\r";
  $vcard_content .= "END:VCARD";
//  echo "<pre><p><b>".$vcard_content."</b></p></pre>";
  return $vcard_content;
}


function make_vcard($vcard_content, $f_name, $l_name){
//echo "Make the card<br>";
  error_reporting(E_ALL);
  ini_set('display_errors','On');
  $card_name = $f_name."_".$l_name;
  $card_name = strtr($card_name, " ", "_");
  $vcard = fopen('vCards/'.$card_name.'.vcf', 'w') or die('vCard creation failed.');
  fwrite($vcard, $vcard_content);
  fclose($vcard);
  $vcard = 'vCards/'.$card_name.'.vcf';
//  echo file_exists($vcard);
  return $vcard;
}


function dl_card($vcard){
  error_reporting(E_ALL);
  ini_set('display_errors','On');

  // Forces download of file
  echo $vcard;
  if (file_exists($vcard)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($vcard));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($vcard));
    ob_clean();
    flush();
    readfile($vcard);
  }else{
    echo "Couldn't find the vCard";
  }
}
