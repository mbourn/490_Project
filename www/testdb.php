<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h1> This is to test db connection and interactions </h1>
<br>

<?php
echo "Make the connection ";
?>

<br>

<?php
//  Make the connection
$con = mysqli_connect("localhost", "from_web", 'Z!s2D#r4%', "490_db");

if (!$con){
    die('Error (' . mysqli_connect_errno() . ')' . mysqli_connect_error());
}

echo 'Success... ' . mysqli_get_host_info($con) . "\n";
mysqli_close($con);
?>
<br>
<br>
<br>
<form action='result.php' method='post'>
First Name: <input type='text' name='f_name'> Last Name: <input type='text' name='l_name'><br>
Phone: <input type='text' name='phone1'> Phone: <input type='text' name='phone2'><br>
Email: <input type='text' name='email'> Twitter: <input type='text' name='twtr'><br>
Instant Messanger <input type='text' name='i_m'><br>
<input type='submit'>

</body>
</html>
