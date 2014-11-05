<?php include "../cgi-bin/auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
<?php 
// Runs the scripts to authenticate with LinkedIn, query the API, and load
// the user and contact info into the database
$last_id=start(); 
?>
</head>
<body>
<header></header>
<main>
<button onclick="window.location=href='https://mbourn.com/create_vcard.php?id=<?php echo $last_id ?>'">Create the vCards</button>


</main>

</body>
</html>


