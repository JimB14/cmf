<?php

namespace App\Controllers; // identy namespace

use \Core\View;
use \App\Models\Post;

/**
 * Posts controller
 *
 * PHP version 7.0
 */
class Posts extends \Core\Controller
{
    /**
     * Show the index page
     * @return void
     */
    public function indexAction()
    {
        //echo "Hello from index method of Posts controller."; exit();

        echo '<p>Query string parameters: <pre>' .
              htmlspecialchars(print_r($_GET, true)) . '</pre></p>';

        // // call getAll() static method of App/Models/Post model
        // $posts = Post::getAll();
        //
        // // call renderTemplate() method of App/Core/View class & pass $posts
        // View::renderTemplate('Posts/index.html', [
        //     'posts' => $posts
        // ]);
    }


    /**
     * Show the add new page
     *
     * @return void
     */
    public function addNewAction()
    {
        echo "Hello from the addNew action in the Posts controller!";
    }


    /**
     * Show the edit page
     *
     * @return void
     */
    public function editAction()
    {
        echo "Hello from the edit action in the Posts controller!";
        echo "<p>Route parameters: <pre>" .
              htmlspecialchars(print_r($this->route_params, true)) . "</pre></p>";
    }



    /**
     * Updates post_status field in `posts` table
     *
     * @return boolean
     */
    public function publish()
    {
        // echo "Connected to publish method!"; exit();

        // retrieve token, post_id and user_id from query string
        $post_token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $post_id = isset($_REQUEST['post_id']) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $user_id = isset($_REQUEST['user_id']) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // set post_status field to publish
        $result = Post::setPostToDisplay($post_token, $post_id, $user_id);

        if($result)
        {
            // get $user object
            $user = User::getUser($user_id);
            $user_email = $user->user_email;
            $user_full_name = $user->user_firstname . ' ' . $user->user_lastname;

            // test
            // echo $user_email . '<br>';
            // echo $user_full_name . "<br>";
            // exit();

            // send thank you email
            $results = Mail::sendThanksForPostEmail($user_email, $user_full_name);

            if($results)
            {
                header("Location: http://challengmyfaith.com");
                exit();
            }
            else
            {
                echo "Error sending email";
                exit();
            }
        }
    }

}
