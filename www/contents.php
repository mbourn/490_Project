<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' type='text/css' href='contents.css'>
<?php include "cgi-bin/auth.php";
/*
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
  }*/
?>
                 
</head>
<body>



<?php
  if($_GET["action"]=='deletedb'){
  delete_db();
}?>

<h1> This is to test db connection and interactions </h1>
<button onclick="window.location='contents.php?action=deletedb';">Delete Database</button>
<br><hr><br>
<h3>Network Table</h3>
<?php
//  Make the connection
$con = mysqli_connect("localhost", "from_web", 'Z!s2D#r4%', "490_db");
if (!$con){
    die('Error (' . mysqli_connect_errno() . ')' . mysqli_connect_error());
}
?>

<table style='border: black 1px;'>
<tr>
  <th>c_id</th><th>c_of</th><th>First Name</th><th>Last Name</th><th>URL</th><th>Email</th><th>Phone</th><th>Phone</th><th>Address</th><th>Twitter Handle</th><th>Instant Messanger</th>
</tr>

<?php
$result = mysqli_query($con, "SELECT * FROM Network");

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
$im=$row['i_m'];

echo "<tr><td>$id</td><td>$of</td><td>$f_name</td><td>$l_name</td><td>$l_url</td><td>$email</td><td>$phone1</td><td>$phone2</td><td>$addr</td><td>$twitr</td><td>$im</td></tr>";   
}
?>
</table>
<br><hr><br>
<h3>Users Table</h3>
<table style='border: black 1px;'>
<tr>
  <th>User ID</th><th>First Name</th><th>Last Name</th>
</tr>

<?php
$result = mysqli_query($con, "SELECT * FROM Users");

while($row = mysqli_fetch_array($result)){
  $u_id=$row['u_id'];
  $f_name=$row['f_name'];
  $l_name=$row['l_name'];
  echo "<tr><td>$u_id</td><td>$f_name</td><td>$l_name</td></tr>";
}
?>
</table>
<br><hr><br>

<form action='testdb.php'>
<input type='submit' value='Add another entry'>
</form>

</body>
</html> 
