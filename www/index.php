<!DOCTYPE html>
<html>
<head>
<?php require "cgi-bin/auth.php" ?>
<link rel="stylesheet" href="main.css">
</head>
<body>
<header>
  <?php render_header(); ?>
</header>
<main id="main">
<div id="index_main_div" class="maindiv">
  <span id="header_expl">
    Welcome to the LinkedIn Contact vCard Generator. If you have a Linked in account then 
    this web page will be able to get the contact information for the people in your network, 
    and turn it into vCards.  These vCards can then be imported into your iPhone, Android 
    phone, Google Contacts, Outlook, etc. To begin, click on Get Started:
  </span>
  <span id="login_btn">
    <button onclick="window.location=href='https://mbourn.com/authorized/authorized.php'">
      Get Started
    </button>
  </span>
</div>
</main>
<footer>
<?php render_footer(); ?>
</body>
</html>
