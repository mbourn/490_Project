<?php
function load_user($user){ 
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";
  
  // Create the connection 
  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Load the contents of the passed PHP object into the database
  $fname = $user['firstName'];
  $lname = $user['lastName'];
  $sql = "INSERT INTO Users (f_name, l_name) 
  		  VALUES($fname, $lname)";

  if( $conn->query($sql) === TRUE){
  	echo "<p>You've been added to the database</p>";
  }else{
  	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }


}
%>
