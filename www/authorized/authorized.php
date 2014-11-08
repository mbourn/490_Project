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


?>
</head>
<body>
<header>
  <section id="header_welcome">
    <h1>LinkedIn Contact to vCard Generator</h1>
  </section>
  <section id="logout">
    <button onclick="window.location=href='https://mbourn.com/?logout'">Logout</button>
  </section>
</header>
<main>

  <div id="privates_div">
    <?php $count=count_privates($last_id); ?>
    <h3>Private accounts</h3>
    <p id="priv_expl">LinkedIn allows its members to set their accounts so that sites like 
                      this one cannot get their profile information, even at the request of 
                      an authorized user.</p>
    <p id="priv_res"><?php echo $count; ?> of your contacts have set their accounts to "private".</p>
  </div>

  <div id="errors_div">
    <h3>Errors</h3>
    <p id="errors_expl">Sometimes there are errors when getting your contacts and loading them
                        into the database.  This is usually caused by the server returning
                        unexpected data.</p>
    <p id="errors_res">There were <?php echo $errors; ?> errors while getting your contacts.</p>
  </div>

  <div id="get_one_div">
    <h3>Create One vCard</h3>
    <section id="get_one_expl">If you would like to create a vCard a specific contact or create
                               a new vCard from scratch, click Continue.
    </section>
    
    <section id="create_one_button">
    <button onclick="window.location=href='https://mbourn.com/one_vcard.php?id=<?php echo $last_id; ?>'">Continue
      </button>
    </section>
  </div>

  <div id="get_multi_div">
    <section id="get_multi_expl">
      <h3>Create Multiple Vcards</h3>
      If you would like to create multiple vCards, but not the whole set, click Continue:<br>
        <button onclick="window.location=href='https://mbourn.com/multi_vcard.php?id=<?php echo $last_id; ?>'">Continue</button>
    </section>
  </div>

  <div id="get_all_div">
    <section id="get_all_expl">
      <h3>Create The Whole Set of vCards</h3>
      If you would like to create and download the entire set of vCards, click Continue:<br>
      <?php echo '<button onclick="window.location=href=\'https://mbourn.com/multi_vcard.php?id='.$last_id.'&action=all\'">Continue</button>'; ?>
  </div>
</main>
<footer></footer>
</body>
</html>


