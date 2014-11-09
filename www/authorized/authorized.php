
<?php include "../cgi-bin/auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
<?php 
// Runs the scripts to authenticate with LinkedIn, query the API, load
// the user and contact info into the database, and redirect to the home page.
  $info_array=start();
  $last_id=$info_array['last_id'];
  $errors=$info_array['errors'];
  $url=$_SERVER['SERVER_NAME'];
  header("Location: https://$url/home.php?last_id=$last_id&errors=$errors");
?>
</head>
<body>
</body>
</html>
