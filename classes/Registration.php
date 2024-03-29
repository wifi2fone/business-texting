<?php error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Handles the user registration
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Registration
{
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection            = null;
    /**
     * @var bool success state of registration
     */
    public  $registration_successful  = false;
    /**
     * @var bool success state of verification
     */
    public  $verification_successful  = false;
    /**
     * @var array collection of error messages
     */
    public  $errors                   = array();
    /**
     * @var array collection of success / neutral messages
     */
    public  $messages                 = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$login = new Login();"
     */
    public function __construct()
    {
        session_start();

        // if we have such a POST request, call the registerNewUser() method
        if (isset($_POST["user_name"])) 
        { 
            $this->registerNewUser($_POST['user_name'], $_POST['user_email'], $_POST['user_password_new'], $_POST['user_password_repeat']);
        }         
        // if we have such a GET request, call the verifyNewUser() method
         else if (isset($_GET["id"]) && isset($_GET["verification_code"])) {
            $this->verifyNewUser($_GET["id"], $_GET["verification_code"]);
        }
    }

    /**
     * Checks if database connection is opened and open it if not
     */
    private function databaseConnection()
    {
        // connection already opened
        if ($this->db_connection != null) {
            return true;
        } else {
            // create a database connection, using the constants from config/config.php
            try {
                // Generate a database connection, using the PDO connector
                // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
                // Also important: We include the charset, as leaving it out seems to be a security issue:
                // @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
                // "Adding the charset to the DSN is very important for security reasons,
                // most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
                $this->db_connection = new PDO("pgsql:host=".DB_HOST." port=".DB_PORT." dbname=".DB_NAME." user=".DB_USER." password=".DB_PASS."");
                return true;
            // If an error is catched, database connection failed
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR;
                return false;
            }
        }
    }

    /**
     * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
     * everything is fine
     */
    private function registerNewUser($user_name, $user_email, $user_password, $user_password_repeat)
    {

        // we just remove extra space on username and email
        $user_name  = trim($user_name);
        $user_email = trim($user_email);

        
        // check provided data validity
        // TODO: check for "return true" case early, so put this first
        if (empty($user_name)) {
            $this->errors[] = MESSAGE_USERNAME_EMPTY;
        } elseif (empty($user_password) || empty($user_password_repeat)) {
            $this->errors[] = MESSAGE_PASSWORD_EMPTY;
        } elseif ($user_password !== $user_password_repeat) {
            $this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
        } elseif (strlen($user_password) < 6) {
            $this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;
        } elseif (strlen($user_name) > 64 || strlen($user_name) < 2) {
            $this->errors[] = MESSAGE_USERNAME_BAD_LENGTH;
        } elseif (!preg_match('/^[0-9]{9,64}$/i', $user_name)) {
            $this->errors[] = MESSAGE_USERNAME_INVALID;
        } elseif (empty($user_email)) {
            $this->errors[] = MESSAGE_EMAIL_EMPTY;
        } elseif (strlen($user_email) > 64) {
            $this->errors[] = MESSAGE_EMAIL_TOO_LONG;
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = MESSAGE_EMAIL_INVALID;

        // finally if all the above checks are ok
        } else if ($this->databaseConnection()) {
            // check if username or email already exists
            $query_check_user_name = $this->db_connection->prepare('SELECT username, email FROM directory WHERE username=:user_name OR email=:user_email');
            $query_check_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            $query_check_user_name->bindValue(':user_email', $user_email, PDO::PARAM_STR);
            $query_check_user_name->execute();
            $result = $query_check_user_name->fetchAll();

            // if username or/and email find in the database
            // TODO: this is really awful!
            if (count($result) > 0) {
                for ($i = 0; $i < count($result); $i++) {
                    $this->errors[] = ($result[$i]['user_name'] == $user_name) ? MESSAGE_USERNAME_EXISTS : MESSAGE_EMAIL_ALREADY_EXISTS;
                }
            } else { 

                
try{
                require_once($_SERVER['DOCUMENT_ROOT'].'/phplam/stripe/init.php');

                \Stripe\Stripe::setApiKey("sk_live_sRMEam1VspiuLY5ktyO7og2Q");


                $customer = \Stripe\Customer::create(

                    array(
                        'email'=>$_POST['user_email'],
                        'description'=>'customer created',
                        'source'=>$_POST['token'],
                        'metadata'=>array(
                            'user_name'=>$_POST['user_name']
                            )
                        
                        )
                    );
                /*if($customer->id)
                    {
                        echo "Customer Created";
                    }*/

                $subscription= \Stripe\Subscription::create(
                    array(
                      "customer" => $customer->id,
                      "plan" => $_POST['plans'])
                    );

                    if($subscription->id)
                    {
                        //echo "Subscription Created";
                    }
                    
                }catch(Exception $e){
                    echo $e.getMessage();
                }

$plan_selected = $_POST['plans'];
$mins = 0;
if($plan_selected=="unlimited"){
$mins = 999;
}else if($plan_selected=="simple"){
    $mins = 500;
}else if($plan_selected=="best"){
    $mins = 250;
}

                // check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
                // if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
                $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

                // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
                // the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
                // compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
                // want the parameter: as an array with, currently only used with 'cost' => XX.
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
                // generate random hash for email verification (40 char string)
                $user_activation_hash = sha1(uniqid(mt_rand(), true));

                // write new users data into database
                $query_new_user_insert = $this->db_connection->prepare('INSERT INTO directory (username, user_password_hash, email,domain_id , cache, user_activation_hash, active,minutes,plan_mins) VALUES(:user_name, :user_password_hash, :user_email,1, 300000, :user_activation_hash, false,:minutes,:plan_mins)');
                $query_new_user_insert->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':user_email', $user_email, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':minutes', $mins,PDO::PARAM_INT);
                $query_new_user_insert->bindValue(':plan_mins', $mins,PDO::PARAM_INT);
                // $query_new_user_insert->bindValue(':user_registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
                $query_new_user_insert->execute();

                // id of new user
                $user_id = $this->db_connection->lastInsertId();


                // write new users the rest of data into database
                $query_new_user_insert1 = $this->db_connection->prepare('INSERT INTO directory_params (directory_id, param_name, param_value) VALUES(:user_id, :user_password_hash, :user_password_hashed)');
                $query_new_user_insert1->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $query_new_user_insert1->bindValue(':user_password_hash', 'md5', PDO::PARAM_STR);
                $query_new_user_insert1->bindValue(':user_password_hashed', $user_password_hash, PDO::PARAM_STR);
                $query_new_user_insert1->execute();


                // write new users the rest of data into database
                $query_new_user_insert2 = $this->db_connection->prepare('INSERT INTO directory_vars (directory_id, var_name, var_value) VALUES(:user_id, :var_name, :var_value)');
                $query_new_user_insert2->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $query_new_user_insert2->bindValue(':var_name', 'user_context', PDO::PARAM_STR);
                $query_new_user_insert2->bindValue(':var_value', 'default', PDO::PARAM_STR);
                $query_new_user_insert2->execute();
                // write new users the rest of data into database
                $query_new_user_insert3 = $this->db_connection->prepare('INSERT INTO directory_vars (directory_id, var_name, var_value) VALUES(:user_id, :var_name, :var_value)');
                $query_new_user_insert3->bindValue(':user_id', $user_id, PDO::PARAM_STR);
                $query_new_user_insert3->bindValue(':var_name', 'accountcode', PDO::PARAM_STR);
                $query_new_user_insert3->bindValue(':var_value', $user_name, PDO::PARAM_STR);
                $query_new_user_insert3->execute();
                // write user data into PHP SESSION [a file on your server]
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['user_email'] = $user_email;
                    $_SESSION['user_logged_in'] = 1;


                if ($query_new_user_insert) {
                    // send a verification email
                    if ($this->sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
                        // when mail has been send successfully
                        $this->messages[] = MESSAGE_VERIFICATION_MAIL_SENT;
                        $this->registration_successful = true;
                    } else {
                        // delete this users account immediately, as we could not send a verification email
                        $query_delete_user = $this->db_connection->prepare('DELETE FROM directory WHERE id=:user_id');
                        $query_delete_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $query_delete_user->execute();

                        // delete this users account immediately, as we could not send a verification email
                        $query_delete_user = $this->db_connection->prepare('DELETE FROM directory_params WHERE directory_id=:user_id');
                        $query_delete_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $query_delete_user->execute();

                        // delete this users account immediately, as we could not send a verification email
                        $query_delete_user = $this->db_connection->prepare('DELETE FROM directory_vars WHERE directory_id=:user_id');
                        $query_delete_user->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $query_delete_user->execute();

                        $this->errors[] = MESSAGE_VERIFICATION_MAIL_ERROR;
                    }
                } else {
                    $this->errors[] = MESSAGE_REGISTRATION_FAILED;
                }
            }
        }
    }

    /*
     * sends an email to the provided email address
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
    {
        $mail = new PHPMailer;

        // please look into the config/config.php for much more info on how to use this!
        // use SMTP or use mail()
        if (EMAIL_USE_SMTP) {
            // Set mailer to use SMTP
            $mail->IsSMTP();
            //useful for debugging, shows full SMTP errors
            //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
            // Enable SMTP authentication
            $mail->SMTPAuth = EMAIL_SMTP_AUTH;
            // Enable encryption, usually SSL/TLS
            if (defined(EMAIL_SMTP_ENCRYPTION)) {
                $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
            }
            // Specify host server
            $mail->Host = EMAIL_SMTP_HOST;
            $mail->Username = EMAIL_SMTP_USERNAME;
            $mail->Password = EMAIL_SMTP_PASSWORD;
            $mail->Port = EMAIL_SMTP_PORT;
        } else {
            $mail->IsMail();
        }

        $mail->From = EMAIL_VERIFICATION_FROM;
        $mail->FromName = EMAIL_VERIFICATION_FROM_NAME;
        $mail->AddAddress($user_email);
        $mail->Subject = EMAIL_VERIFICATION_SUBJECT;

        $link = EMAIL_VERIFICATION_URL.'?id='.urlencode($user_id).'&verification_code='.urlencode($user_activation_hash);

        // the link to your register.php, please set this value in config/email_verification.php
        $mail->Body = 'Hello, you\'ve been registered successfully... you can just use our services or for more security you can visit the following link <br> '.EMAIL_VERIFICATION_CONTENT.' '.$link;

        if(!$mail->Send()) {
            $this->errors[] = MESSAGE_VERIFICATION_MAIL_NOT_SENT . $mail->ErrorInfo;
            return false;
        } else {
            $mail->ClearAllRecipients( );
            $mail->AddAddress(EMAIL_SMTP_USERNAME);
            $mail->Body = 'New User... His Mail is: '.$user_email.', And his id is '.$user_id;
            $mail->Send();
            $mail->ClearAllRecipients( );
            $mail->AddAddress('bardoana27@yahoo.com');
            $mail->Body = 'New User... His Mail is: '.$user_email.', And his id is '.$user_id;
            $mail->Send();
            return true;
        }
    }

    /**
     * checks the id/verification code combination and set the user's activation status to true (=1) in the database
     */
    public function verifyNewUser($user_id, $user_activation_hash)
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // try to update user with specified information
            $query_update_user = $this->db_connection->prepare('UPDATE directory SET active = true, user_activation_hash = NULL WHERE id = :user_id AND user_activation_hash = :user_activation_hash');
            $query_update_user->bindValue(':user_id', intval(trim($user_id)), PDO::PARAM_INT);
            $query_update_user->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
            $query_update_user->execute();

            if ($query_update_user->rowCount() > 0) {
                $this->verification_successful = true;
                $this->messages[] = MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL;
            } else {
                $this->errors[] = MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL;
            }
        }
    }
}
