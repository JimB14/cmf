<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Models\UserPending;
use \App\Mail;
use \App\Models\Broker;



class Register extends \Core\Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo "(before) ";
        //return false;  // prevents originally called method from executing
    }


    protected function after()
    {
        //echo " (after)";

    }



    public function indexAction()
    {
        View::renderTemplate('Register/index.html', [
            'registerindex' => 'active'
        ]);
    }



    public function checkIfEmailAvailableAction()
    {
        // store POST variable from Ajax script
        $email = $_POST['email'];

        // check for email using User model method
        $response = User::checkIfAvailable($email);

        // return $response value ('available' or 'not available') to
        // Ajax method for processing
        echo $response;
    }




    /**
     * adds new user to users table, users_pending table & sends verification email
     *
     * @return void
     */
    public function registerNewUser()
    {
        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
           return false;
           exit();
        }

        // add new user to users table; get data & store in $results array
        $results = User::addNewUser();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // echo "<br><br>";
        // exit();

        // assign values from $results associative array to variables
        $token = $results['token'];
        $user_id = $results['user_id'];

        // get all fields of new user data (need email & first_name)
        $user = User::getUser($user_id);

        // assign values from user object to variables
        $email = $user->user_email;
        $first_name = $user->user_firstname;

        // test
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();

        // add new user to users_pending table & pass $token & $user_id
        $results = $this->addToUsersPending($token, $user_id);

        if($results)
        {
            // send verification email to new user's email address
            $result = Mail::sendAccountVerificationEmail($token, $user_id, $email, $first_name);

            if($result)
            {
              // define message
              $success_registration1 = 'You have successfully registered!';

              $success_registration2 = "Please check your email to verify your
                account. If you do not receive an email from
                noreply@challengemyfaith.com in the next few minutes, please
                check your spam folder and white-list challengemyfaith.com.";

              View::renderTemplate('Success/index.html', [
                  'success_registration'  => 'true',
                  'success_registration1' => $success_registration1,
                  'success_registration2' => $success_registration2
              ]);
            }
            else
            {
                echo "Error. Verification email not sent";
                exit();
            }
        }
    }



    /**
     * adds new user to users_pending table
     *
     * @param string $token     Unique string
     * @param integer $user_id  The new user's ID
     */
    public function addToUsersPending($token, $user_id)
    {
        // add user data to users_pending table
        $results = UserPending::addUserToUsersPending($token, $user_id);

        // return to Controller
        return $results;
    }





    /**
     * new user clicks link in email
     *
     * @return string boolean
     */
    public function verifyAccount()
    {
        // retrieve token & user_id & pass to verifyNewUserAccount method below
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $user_id = isset($_REQUEST['user_id']) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // verify match in `users_pending`, if true, set active = 1 & delete record from users_pending table
        $result = UserPending::verifyNewUserAccount($token, $user_id);

        // display success in view
        if($result)
        {
            // define message
            $acct_activated1 = "Congratulations! Your account has been activated.";

            // show success message
            View::renderTemplate('Success/index.html', [
                'acct_activated1' => $acct_activated1,
                'acct_activated'  => 'true'
            ]);
        }
        else
        {
            echo "An error occurred while verifying your account. Please try again.";
            exit();
        }
    }



    /**
     * Registers new broker
     *
     * @return boolean  Success or failure
     */
    public function brokerRegistration()
    {
        // retrieve token in order to pass to verifyNewUserAccount method below
        $user_id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // insert user's broker data into brokers table; get new Broker's ID
        $result = Broker::addNewBroker($user_id);

        // test
        // echo '<pre>';
        // print_r($result);
        // echo '</pre>';
        // exit();

        if($result)
        {
            // render security questions form
            View::renderTemplate('Register/security-questions.html', [
                'user_id' => $user_id
            ]);
        }
    }






    public function postSecurityAnswers()
    {
        // retrieve query string variable
        $user_id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // store security answers in users table
        $result = User::storeSecurityAnswers($user_id);

        // get user data
        $user = User::getUser($user_id);

        if($result)
        {
            // update first_login status to false (first_login = 0)
            $result = User::updateFirstLoginStatus($user_id);

            if($result)
            {
              $register_success1 = "Your registration is complete!";

              $register_success2 = "To begin, Log In. On the next screen, click
                'Admin' in the top right part of the Menu. Then, click
                'Admin Panel'. You must add a new agent before posting your
                first listing.";

              $register_success3 = "Click here to Log In.";

              $register_success4 = "Remember, on the next screen, click 'Admin'
                in the Menu.";

                $register_success5 = "Remember, you must add a new agent before
                  you can post a listing.";

              View::renderTemplate('Success/index.html', [
                  'register_success'  => 'true',
                  'register_success1' => $register_success1,
                  'register_success2' => $register_success2,
                  'register_success3' => $register_success3,
                  'register_success4' => $register_success4,
                  'register_success5' => $register_success5,
                  'first_name'        => $user->first_name,
                  'last_name'         => $user->last_name
              ]);
            }
            else
            {
                echo "Error updating user login status.";
                exit();
            }
        }
        else
        {
            echo "Error inserting security data.";
            exit();
        }
    }
}
