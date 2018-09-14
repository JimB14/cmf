<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Broker;
use \App\Mail;
use \App\Models\User;


/**
 * Logout controller
 *
 * PHP version 7.0
 */
class Logout extends \Core\Controller
{
    /**
     * logs user out
     * @return boolean
     */
    public function indexAction()
    {
        // retrieve query string data
        $value = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // if SESSION is not set & user attempts to logout
        if(!isset($_SESSION['user']))
        {
            header("Location: /login");
            exit();
        }
        else
        {
            // get user data
            $user = User::getUser($_SESSION['user_id']);

            if($user)
            {
                // send login notification email
                $result = Mail::LogoutNotification($user);
            }

            unset($_SESSION['user']);
            unset($_SESSION['loggedIn']);
            unset($_SESSION['user_id']);
            unset($_SESSION['access_level']);
            unset($_SESSION['full_name']);
            session_destroy();

            $message = "You have been logged out.";

            // render view
            View::renderTemplate("Success/index.html", [
                'message' => $message,
                'logout'  => 'true'
            ]);
        }
    }

}
