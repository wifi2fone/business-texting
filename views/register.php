<?php 

include('_header.php'); ?>

<script src="https://checkout.stripe.com/checkout.js"></script>
<!-- show registration form, but only if we didn't submit already -->
<?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
<form method="post" action="register.php" id="registerform">
    <label for="user_name"><?php echo WORDING_REGISTRATION_USERNAME; ?></label>
    <input id="user_name" type="text" pattern="[0-9]{9,64}" name="user_name" required />

    <label for="user_email"><?php echo WORDING_REGISTRATION_EMAIL; ?></label>
    <input id="user_email" type="email" name="user_email" required />

    <label for="user_password_new"><?php echo WORDING_REGISTRATION_PASSWORD; ?></label>
    <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />

    <label for="user_password_repeat"><?php echo WORDING_REGISTRATION_PASSWORD_REPEAT; ?></label>
    <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
    <div><label>Choose Plan</label></div>
    <div><select name="plans" id="plans">
<option value="unlimited">Unlimited Calls To USA  $3.99</option>
        <option value="best">Best Value $1.99  (250 mins)</option>
        <option value="simple">Simple Plan $2.99  (500 mins)</option>
    </select></div>
    <input type="hidden" name="token" id="token" value="">
    <div><label><input type="checkbox" name="check" id="check" value="check">I Agree to <a target="_blank" href="http://www.wifi2fone.com/terms-conditions">Wifi2fone.com</a> terms and conditions *</label></div>

<!--     <img src="tools/showCaptcha.php" alt="captcha" />

    <label><?php echo WORDING_REGISTRATION_CAPTCHA; ?></label>
    <input type="text" name="captcha" required /> -->

   <!-- <input type="submit" name="register" value="<?php echo WORDING_REGISTER; ?>"<!-- /> -->

 <div>     
<button id="customButton">Register and Subscribe</button></div>

</form>





<script>
var handler = StripeCheckout.configure({
  key: 'pk_live_lEVWhR47kal4WBxf1GZnqE0L',
  image: '/phplam/phone.png',
  locale: 'auto',
  token: function(token) {
    // You can access the token ID with `token.id`.
    // Get the token ID to your server-side code for use.
    document.getElementById('token').value = token.id;
    document.getElementById("registerform").submit();
  }
});

document.getElementById('customButton').addEventListener('click', function(e)
 {

    if(document.getElementById('check').checked)
    {
  // Open Checkout with further options:

var plan = document.getElementById('plans').value;
var amount = 0;
if(plan=="best"){
plan = "Best Value $1.99  (250 mins)";
amount = 1.99;
}else if(plan=="simple"){
plan = "Simple Plan $2.99  (500 mins)";
amount = 2.99;
}else{
	plan = "Unlimited Calls To USA  $3.99";
	amount = 3.99;
}


      handler.open({
        name: 'WiFi2fone',
        description: plan,
        currency: 'usd',
        amount: amount*100,
        email:document.getElementById('user_email').value
      });
    }else{
    	alert('Please agree with terms!');
    }
    
    e.preventDefault();


});

// Close Checkout on page navigation:
window.addEventListener('popstate', function() {
  handler.close();
});
</script>
</form>
<?php } ?>

    <a href="index.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('_footer.php'); ?>
