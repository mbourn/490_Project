
<?php
/////////////////////////////////////////////////////////////////////////////////////////
//Function to kick off the authentication and authorization process
function start(){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
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
 
  $user = fetch('GET', '/v1/people/~');
  // Get the user's network
  $network = fetch('GET', '/v1/people/~/connections:(first-name,last-name,site-standard-profile-request)');
  // Add the user to the Users table, return the user's primary key
  $last_id = load_user($user);
  // Add the user's network to the Network table with the user's primary
  // key as the foreign key.
  $errors = load_network($network, $last_id);
  var_dump($last_id);
  var_dump($errors);
  $return_info = array(
    'last_id' => $last_id,
    'errors' => $errors,
  );
  return $return_info;
}

/////////////////////////////////////////////////////////////////////////////////////////
//  Function to retrieve an authorization code
function getAuthorizationCode() {
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
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

/////////////////////////////////////////////////////////////////////////////////////////
// Function to get an access token for using the API     
function getAccessToken() {
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
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
 
 // Function to query the API 
function fetch($method, $resource, $body = '') {
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
 
    $opts = array(
        'http'=>array(
            'method' => $method,
            'header' => "Authorization: Bearer " . $_SESSION['access_token'] . "\r\n" . "x-li-format: json\r\n"
        )
    );
 
    // Need to use HTTPS
    $url = 'https://api.linkedin.com' . $resource;
 
    // Append query parameters (if there are any)
    //if (count($params)) { $url .= '?' . http_build_query($params); } //old code
 
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    // And use OAuth 2 access token as Authorization
    $context = stream_context_create($opts);
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
    // Native PHP object, please
    return json_decode($response);
}

/////////////////////////////////////////////////////////////////////////////////////////
// This function takes the user php object returned from LinkedIn and adds
// the relevent values to the database 
function load_user($user){ 
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  
  // Load the contents of the passed PHP object into the database
  $fname = $user->firstName;
  $lname = $user->lastName;
  $sql = "INSERT INTO Users (f_name, l_name) VALUES('$fname', '$lname')";
  if( $conn->query($sql) === TRUE){
  }else{
  	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }
  $last_id = $conn->insert_id;
  mysqli_close($conn);
  return $last_id;
}

/////////////////////////////////////////////////////////////////////////////////////////
// This function takes the network PHP object returned by the LinkedIn
// server and adds the relevent values to the database.  It also takes an
// integer that is used to add the foreign key that references the user
// whose contacts these are.
function load_network( $network, $last_id ){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
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

////////////////////////////////////////////////////////////////////////////////////////
// Renders the page header
function render_header(){
  echo '<div id="header-div"><div id="header_cont">';
  echo '<div id="header_img"><img src="images/logo.png" height=150px>';
  if( $_GET['last_id'] ){
    echo '<br><button id="logout_btn" onclick="window.location=';
    echo 'href=\'https://mbourn.com/?action=logout\'">Logout</button>';
  }
  echo '</div></div></div>';
}

////////////////////////////////////////////////////////////////////////////////////////
// Renders the page footer
function render_footer(){
  echo '<div id="footer_div">';
  echo "<br>Copyright &copy; 2013-" . date("Y") . " M. Bourn";
  echo "</div>";
}


////////////////////////////////////////////////////////////////////////////////////////
// Renders the html to display the, search results, search dialogue, text field, and 
// button. Takes a boolean showing whether or not a search has been performed, the 
// results of that search (NULL if $has_searched is FALSE), and the u_id of the current 
// user as the arguments.
function render_search_div($has_searched, $result, $last_id){
  echo '<div id="search_div">';
  echo '<div id="search_expl">';
  echo 'To search for a contact in the database, enter either the person\'s first or last name and click on Search.';
  echo '</div>';
  echo '<div id="search_form_p">';
  echo '<form action="one_vcard.php" method="POST" id="search_form">';
  echo 'Contact Name: <input type="text" name="c_name"><br>';
  echo '<input type="hidden" name="last_id" value="'.$last_id.'">';
  echo '<input class="btn" type="submit" id="get_one_search_btn" name="search_btn" value="Search">';
  echo '</form></div>';
  echo '<div id="search_result_div">';
  if($has_searched && $result->num_rows == 0){
    echo '<div id="search_result_fail"><b>That contact was not found.</b><br> Your contact may 
           have set his or her profile to private or there may have been an error entering 
           the information into the database.<br>Please try again.</div></div>';
  }elseif($has_searched && $result->num_rows > 0){
    echo '<div id="search_result_found">';
    echo '<b>Found:</b><br>';
    while( $row = mysqli_fetch_array($result)){
      $f_name = $row['f_name'];
      $l_name = $row['l_name'];
      $c_id = $row['c_id'];
      echo '<form action="one_vcard.php" method="POST" id="search_form">';
      echo '<input type="hidden" name="return_addr" value="https://'.$_SERVER['SERVER_NAME'].'/one_vcard.php?id='.$last_id.'">';
      echo '<input type="hidden" name="c_id" value="'.$c_id.'">';
      echo $f_name.' '.$l_name.' <input class="btn" type="submit" name="select_btn" value="Edit"> <input class="btn" type="submit" name="select_btn" value="Download"><br></form>';
    }    
    echo '</div></div>';
  }
}

/////////////////////////////////////////////////////////////////////////////////////////
// Creates the html for displaying all of the user's contacts in a list. Each item in the
// list will have a check box for selecting it to be included in the zipped file that
// will be created and force downloaded when submit is clicked.  Takes the user's id and
// the query results as arguments.
function render_multi_div($result, $last_id){
    

  $count = 0;
  $color_ctr = 0;
  $color = "lightgrey";
  // $array_size = $result->num_rows;
  echo '<table id="multi_table">';
  while($row = mysqli_fetch_array($result)){
    // Set variables
    $f_name = $row['f_name'];
    $l_name = $row['l_name'];
    $c_id = $row['c_id'];
  
    // Choose span background color
    if($color_ctr > 1){
      if($color == "lightgrey"){
        $color = 'white';
      }else{
        $color = 'lightgrey';
      }
      $color_ctr = 0;
    }

    // Print list of contacts
    if( $count % 2 == 0){
      echo '<tr>';
    }
    echo '<td><span style="background-color:'.$color.';display:inline-block;width:300px">';
    echo '<input type="checkbox" name="contact[]" value="'.$c_id.'">';
    echo $f_name." ".$l_name."</span></td>";
    echo '<td><form action="edit.php" method="POST" id="multi_edit_form">';
    echo '<input type="hidden" name="last_id" value="'.$last_id.'">';
    echo '<input type="hidden" name="return_addr" value="https://'.$_SERVER['SERVER_NAME'].'/'.'multi_vcard.php?id='.$last_id.'">';
    echo '<input type="hidden" name="c_id" value="'.$c_id.'">';
    echo '<input class="btn" type="submit" name="edit_btn" value="Edit"></form></td>';
    if( $count % 2 != 0 ){
      echo '</tr>';
    }
    $count++;
    $color_ctr++;
  }
}

/////////////////////////////////////////////////////////////////////////////////////////
// Searches for an individual in the Network database
// Takes a string  (either the first or last name of the contact) and the u_id of the 
// user as the arguments. Does not sanitize or parameterize the data. Vulnerable.
function find_contact($name, $c_of){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  $conn = create_db_connection();
  $sql = "SELECT * FROM Network WHERE (f_name = '$name' OR l_name = '$name') AND c_of = $c_of ORDER BY 2";
  $result = mysqli_query($conn, $sql);
  return $result;
}

/////////////////////////////////////////////////////////////////////////////////////////
function edit_individ_contact($contact){

}

/////////////////////////////////////////////////////////////////////////////////////////
// Creates and returns a connection to the local database
function create_db_connection(){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";
                
  // Create the connection 
  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }
  return $conn;
}

/////////////////////////////////////////////////////////////////////////////////////////
// Creates a set of vCards.  Takes an indexed array of c_ids as the argument
function make_multi_set($c_id_array){

  // Create db connection and variables
  $conn = create_db_connection();
  $array_size = count($_POST['contact']);

  // Create directory for the vCards
  $dir_name = md5(time());
  $dir_path = "vCards/$dir_name/";
  if( !mkdir($dir_path));{
  }   

  // Create the zip file
  $zip = new ZipArchive();
  $zip_file = "vCards/$dir_name/linkedin_contacts.zip";
  if( $zip->open($zip_file, ZipArchive::CREATE)!==TRUE){
    exit("Cannot open <$zip_file>\n");
  }   
              
  // Go through the array, creating a vcard for each contact and adding it to the zip file
  for($i=0;$i<$array_size;$i++){
    $c_id = $c_id_array[$i];
    $sql = "SELECT * FROM Network WHERE c_id = $c_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    $vcard_content = make_vcard_content($row['f_name'], $row['l_name'], $row['l_url']);
    $vcard = make_vcard($vcard_content, $row['f_name'], $row['l_name']);
    $zip->addFile($vcard);
  }          
  $zip->close();
  dl_card($zip_file);
}   
 
/////////////////////////////////////////////////////////////////////////////////////////
// Creates a vCard for every contact of the user, places all of the vCards into
// a zip file and then force downloads the zip file to the user
function make_all($last_id){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  
  // Create and submit query
  $sql="SELECT f_name, l_name, l_url FROM Network WHERE c_of = $last_id";
  $result = mysqli_query($conn, $sql);
   
  // Create zip file to hold all the vCards
  $dir_name = md5(time());
  $dir_path = "vCards/".$dir_name."/";
  $zip = new ZipArchive();
  $zip_file = $dir_path.'linkedin_contacts.zip';
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
  //delete_files();
  exit;
}

/////////////////////////////////////////////////////////////////////////////////////////
// Make and force download a single vCard
// It takes the c_id of the contact as an argument
function make_one($c_id){
  $conn = create_db_connection();
  $sql = "SELECT f_name, l_name, l_url FROM Network WHERE c_id = $c_id";
  $result = mysqli_query($conn, $sql);
  $res = mysqli_fetch_array($result);
  $f_name = $res['f_name'];
  $l_name = $res['l_name'];
  $l_url = $res['l_url'];
  $vcard_content = make_vcard_content($f_name, $l_name, $l_url);
  $vcard = make_vcard($vcard_content, $f_name, $l_name);
  dl_card($vcard);
  
  /*
  //parameterize the input from user, defend against SQL Injection
  $stmt = $db->prepare('update people set name = ? where id = ?');
  $stmt->bind_param('si',$name,$id);
  $stmt->execute();*/
}    

/////////////////////////////////////////////////////////////////////////////////////////
// Creates the string used as the contents of the vCard
// Takes the different fields as arguments
// Will be modified in the future to take an array when program is expanded to include
// more fields
function make_vcard_content($f_name, $l_name, $l_url){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  $vcard_content = "BEGIN:VCARD\r";
  $vcard_content .= "VERSION:3.0\r";
  $vcard_content .= "N:".$l_name.";".$f_name.";;\r";
  $vcard_content .= "item1.URL;tpe=pref:".$l_url."\r";
  $vcard_content .= 'item1.X-ABLabel:_$!<LinkedInPage>!$_\r';
  $vcard_content .= "X-ABShowAs:COMPANY\r";
  $vcard_content .= "END:VCARD";
  return $vcard_content;
}

/////////////////////////////////////////////////////////////////////////////////////////
// Make the vCard file in the local directory vCards
// Takes the vCard contents and the contact's first and last names as arguments
// The names are used to generate the file name.  If I were more clever I'd just parse
// the contents string but that sounded like a lot of work.
function make_vcard($vcard_content, $f_name, $l_name){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  
  $card_name = $f_name."_".$l_name;
  $card_name = strtr($card_name, " ", "_");
  //echo "<br><hr><br>". $card_name;
  $dir_path = "vCards/".md5(time()).'/';
  //echo "<br>".$dir_path;
  
  if( !file_exists($dir_path)){
    mkdir($dir_path);
  }
  $vcard = fopen("$dir_path".$card_name.'.vcf', 'w') or die('vCard creation failed.');
  fwrite($vcard, $vcard_content);
  fclose($vcard);
  $vcard = "$dir_path".$card_name.'.vcf';
  return $vcard;
}

/////////////////////////////////////////////////////////////////////////////////////////
//  Force downloads a vCard to the user
//  Takes a string containing the path to the file to be downloaded as an argument
function dl_card($vcard){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');

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

/////////////////////////////////////////////////////////////////////////////////////////
// Count the number of contacts that have set their profiles to "private" 
// and return that number
function count_privates($last_id){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  // Create the connection
  $conn = create_db_connection();
  $sql = "SELECT f_name FROM Network WHERE f_name = 'private' AND c_of = $last_id";     
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

/////////////////////////////////////////////////////////////////////////////////////////
// Resets the session and redirects the user back to the index page
function logout(){
  session_start();
  $_SESSION = array();
  if (ini_get("session.use_cookies")){
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() -42000, $params["path"], 
      $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
  unset($_COOKIE['linkedin']);
  setcookie('linkedin', "", time()-3600, "/", "mbour.com");
}

/////////////////////////////////////////////////////////////////////////////////////////
// Delete all of the files in the vCards directory
function delete_files(){
  $files = glob('vCards/*');
  foreach($files as $file){
    if(is_file($file)){
      unlink($file);
    }   
  }
}

/////////////////////////////////////////////////////////////////////////////////////////
// This deletes the entire contents of both tables in the database and resets all
// primary keys to 0
function delete_db(){
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
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
