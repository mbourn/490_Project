<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h1> This is to test db connection and interactions </h1>
<br>
<?php
//  Make the connection
$con = mysqli_connect("localhost", "from_web", ')M(N*B&V', "proj490");

if (!$con){
    die('Error (' . mysqli_connect_errno() . ')' . mysqli_connect_error());
}

$f_name = $_POST["f_name"];    
$l_name = $_POST["l_name"];    
$email = $_POST["email"];    
$phone1 = $_POST["phone1"];    
$phone2 = $_POST["phone2"];    
$twitr = $_POST["twitr"];    

if(mysqli_query($con, "INSERT INTO CONNECTIONS (f_name, l_name, phone1, phone2, email) VALUES ('$f_name', '$l_name', '$phone1', '$phone2', '$email')")===TRUE){
  echo "Contact info stored in database.<br>";
}
mysqli_close($con);

?>

<hr>
Welcome <?php echo $_POST["f_name"] . " " . $_POST["l_name"]; ?><br>
Your email address is: <?php echo $_POST["email"]; ?> <br>
<br>

<hr>

<br>
<form action='contents.php'>
<input type='submit' value='Dump the Database'>
</form>

</body>
</html> 
