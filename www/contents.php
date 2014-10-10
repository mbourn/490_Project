<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' type='text/css' href='contents.css'>
</head>
<body>
<h1> This is to test db connection and interactions </h1>
<br><hr><br>

<?php
//  Make the connection
$con = mysqli_connect("localhost", "from_web", ')M(N*B&V', "proj490");
if (!$con){
    die('Error (' . mysqli_connect_errno() . ')' . mysqli_connect_error());
}
?>

<table style='border: black 1px;'>
<tr>
  <th>First Name</th><th>Last Name</th><th>Email</th><th>Phone</th><th>Phone</th><th>Address</th><th>Twitter Handle</th><th>Instant Messanger</th>
</tr>

<?php
$result = mysqli_query($con, "SELECT * FROM CONNECTIONS");

while($row = mysqli_fetch_array($result)){
$f_name=$row['f_name'];
$l_name=$row['l_name'];
$email=$row['email'];
$addr=$row['addr'];
$phone1=$row['phone1'];
$phone2=$row['phone2'];
$addr=$row['addr'];
$twitr=$row['twitr'];
$im=$row['im'];

echo "<tr><td>$f_name</td><td>$l_name</td><td>$email</td><td>$phone1</td><td>$phone2</td><td>$addr</td><td>$twitr</td><td>$im</td></tr>";   
}
?>
</table>
<br><hr><br>
<form action='testdb.php'>
<input type='submit' value='Add another entry'>
</form>

</body>
</html> 
