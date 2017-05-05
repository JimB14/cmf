<?php
/**
 * Front controller
 *
 * PHP  version 7.0
 */

/**
 * Composer
 */
// loads class files; eliminates need to require files to use the class
require '../vendor/autoload.php';

// must come AFTER autoloader for classes to be known to SESSION variable
session_start();

$_SESSION['loggedIn'] = false;

// resource: http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
// destroy session after 45 minutes of inactivity
// if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 10)) /* 2700 */
// {
//     // last request was more than 45 minutes ago
//     session_unset();     // unset $_SESSION variable for the run-time
//     session_destroy();   // destroy session data in storage
//
//     $message = "You have been logged out due to inactivity for 45 minutes.";
//
//     $message2 = "Stop. You must log back in. Do not add, delete or modify data.";
//
//     // render view
//     \Core\View::renderTemplate("Success/index.html", [
//         'message'   => $message,
//         'message2'  => $message2
//     ]);
// }
// $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp



/**
 * Twig (remove if running Twig 2.0)
 */
// Twig_Autoloader::register();


/**
 * Error and Exception handling
 */
error_reporting(E_ALL); // — Sets which PHP errors are reported
// set_error_handler — Sets a user-defined error handler function
// call static errorHandler() method in Core/Error class
set_error_handler('Core\Error::errorHandler');
// set_exception_handler — Sets a user-defined exception handler function
// call static exceptionHandler() method in Core/Error class
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add routes (argument 1: route, argument 2: parameters (controller & action))
$router->add('', ['controller' => 'Home',  'action' => 'index']);
$router->add('terms-conditions', ['controller' => 'Terms', 'action' => 'index']);

$router->add('contact', ['controller' => 'Contact', 'action' => 'index']);
$router->add('about', ['controller' => 'About', 'action' => 'index']);
$router->add('testimonials', ['controller' => 'Testimonials', 'action' => 'index']);
$router->add('register', ['controller' => 'Register', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'index']);
$router->add('logout', ['controller' => 'Logout', 'action' => 'index']);

$router->add('login/login-user', ['controller' => 'Login', 'action' => 'login-user']);

$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']); // assign the namespace
$router->add('admin/{controller}/{action}/{id:\d+}', ['namespace' => 'Admin']); // assign the namespace

$router->add('{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}'); // 'id' can be anything
$router->add('{controller}/{action}/{id:\d+}'); // controller, action and id can be in any order



// call dispatch method of Router class
$router->dispatch($_SERVER['QUERY_STRING']);
