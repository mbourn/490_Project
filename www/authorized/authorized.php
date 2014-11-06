<?php include "../cgi-bin/auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
<?php 
// Runs the scripts to authenticate with LinkedIn, query the API, and load
// the user and contact info into the database
$info_array=start();
$last_id=$info_array['last_id'];
$errors=$info_array['errors'];
var_dump($errors);
var_dump($last_id);
?>
</head>
<body>
<header></header>
<main>
<div id="privates_div">
<?php $count=count_privates($last_id); ?>
<h3>Private accounts</h3>
<p id="priv_expl">LinkedIn allows its members to set their accounts so that sites like this one cannot get their profile information, even at the request of an authorized user.</p>
<p id="priv_res"><?php echo $count; ?> of your contacts have set their accounts to "private".</p>
</div>
<div id="errors_div">
<h3>Errors</h3>
<p id="errors_expl">Sometimes there are errors when getting your contacts and loading them into the database.  This is usually caused by the server returning unexpected data.</p>
<p id="errors_res">We encountered <?php echo $errors; ?> while getting your contacts.</p>
</div>
<div id="get_one_div">
<h3>Create One vCard</h3>
<section id="get_one_expl">If you would like to create a single vCard for one of your contacts, please enter your contact's first and last name and then click "Search".</section>
<section id="get_one_button">
<form action="edit_one.php" method="get">
<input type="search" name="f_name" placeholder="First Name">
<input type="search" name="l_name" placeholder="Last Name">
<input id="button" type="submit" value="search">
</form>
</section>
<button onclick="window.location=href='https://mbourn.com/create_vcard.php?id=<?php echo $last_id ?>'">Create the vCards</button>


</main>

</body>
</html>


