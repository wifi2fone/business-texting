<?php include('_header.php');

$Med_var = new getuserinfo();
$Med_var = $Med_var->get_info();

?>

<div class="logged-in">
	<h2>Welcome
	<?php
	// if you need the user's information, just put them into the $_SESSION variable and output them hereWORDING_YOU_ARE_LOGGED_IN_AS .
	echo  htmlspecialchars($_SESSION['user_name']) . "<br />";
	//echo WORDING_PROFILE_PICTURE . '<br/><img src="' . $login->user_gravatar_image_url . '" />;
	// echo WORDING_PROFILE_PICTURE . '<br/>' . $login->user_gravatar_image_tag;
	?>
	</h2>
	<br>
	<br>
	<p>Current Plan: <?php echo $Med_var->plan_mins; ?></p>
	<br>
	<p>Mins Used: <?php echo ($Med_var->plan_mins - $Med_var->minutes); ?></p>
	<br>
	<p>Mins Balance: <?php echo $Med_var->minutes; ?></p>
	<br>
	<br>
    <a href="javascript:;">Click for call log</a>
    <br>
    <br>
    <a href="index.php?logout"><?php echo WORDING_LOGOUT; ?></a>
    <a href="edit.php"><?php echo WORDING_EDIT_USER_DATA; ?></a>
</div>

<?php include('_footer.php'); ?>
