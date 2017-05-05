<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Page;
use \App\Mail;
use \App\Config;
use \App\Models\Recaptcha;
use \App\Models\Comment;
use \App\Models\Post;
use \App\Models\User;



/**
 * Contact controller
 *
 * PHP version 7.0
 */
class Comments extends \Core\Controller
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


    /**
     * inserts post comment into DB, alerts comment author & sends email to post author
     *
     * @return
     */
    public function submitPostCommentAction()
    {
        // check if logged in
        if(!$_SESSION['user_id'])
        {

            // render view
            View::renderTemplate('Error/index.html', [
                'comment_error_msg' => 'true'
            ]);
        }
        else
        {
            // retrieve string query
            $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_STRING): '';
            $user_id = ( isset($_REQUEST['user_id']) ) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING): '';

            // test
            // echo '<pre>';
            // print_r($_REQUEST);
            // echo '</pre>';
            // exit();

            // get user data
            $user = User::getUser($user_id);

            // insert parent comment into `comments` table
            $results = Comment::submitPostComment($post_id, $user);

            if($results['result'])
            {
                // create array for comment data (use in Mail function)
                $comment_data = [
                    'comment_author'  => $results['comment_author'],
                    'comment'         => $results['comment'],
                    'comment_id'      => $results['comment_id'],
                    'comment_token'   => $results['comment_token'],
                    'comment_user_id' => $results['comment_user_id'],
                    'comment_token'   => $results['comment_token']
                ];

                // increment posts.comment_count field
                $result = Post::incrementCommentCount($post_id);

                if($result)
                {
                    // get post data
                    $post = Post::getPost($post_id);

                    // store post author ID in variable
                    $post_author = $post->post_author;

                    // get author data
                    $author = User::getAuthor($post_author);

                    // send email to author and pass author data, post data & comment data
                    $result = Mail::commentNotificationToAuthor($author, $post, $comment_data);

                    if($result)
                    {
                        $message = "Your comment was successfully submitted. You will be
                          notified by email when it is approved and published.";

                        echo '<script>';
                        echo 'alert("'.$message.'")';
                        echo '</script>';

                        // redirect user to Views/Admin/index.html
                        echo '<script>';
                        echo 'window.location.href="/home/get-post/'.$post_id.'"';
                        echo '</script>';
                        exit();
                    }
                }
            }
            else
            {
                echo "Error submitting comment.";
                exit();
            }
        }
    }



    /**
     * publish new post comment by updating comments.comment_approved value to 1
     *
     * @return Boolean
     */
    public function publishCommment()
    {
        // get string query data
        $comment_token = ( isset($_REQUEST['comment_token']) ) ? filter_var($_REQUEST['comment_token'], FILTER_SANITIZE_STRING): '';
        $comment_id = ( isset($_REQUEST['comment_id']) ) ? filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $post_author = ( isset($_REQUEST['post_author_id']) ) ? filter_var($_REQUEST['post_author_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $comment_user_id = ( isset($_REQUEST['comment_user_id']) ) ? filter_var($_REQUEST['comment_user_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT): '';

        if($comment_id != '' && $comment_token != '')
        {
            // update comments.comment_approved to true (1)
            $result = Comment::approveComment($comment_id, $comment_token);

            if($result)
            {
                // get author data to get email address
                $author = User::getAuthor($post_author);

                // get user data to get email address
                $user = User::getUser($comment_user_id);

                // get post data
                $post = Post::getPost($post_id);

                // notify comment author and copy post author
                $result = Mail::commentApprovalNotificationToUser($user, $author, $post);
            }
        }
        else
        {
            echo "Error. Required data missing or invalid.";
            exit();
        }
    }


    /**
     * increases value of comment_voteup field for referenced post & returns user to same page
     *
     * @return Boolean
     */
    public function voteUp()
    {
        // check if logged in
        if($_SESSION['user_id'])
        {
          // get string query data
          $comment_id = ( isset($_REQUEST['comment_id']) ) ? filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT): '';
          $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT): '';

          // store user ID in variable
          $user_id = $_SESSION['user_id'];

          // increment comment_voteup
          $result = Comment::voteUp($comment_id);

          if($result)
          {
              header("Location: /home/get-post?post_id=$post_id&user_id=$user_id");
              exit();
          }
          else
          {
              echo "Error. Unable to execute query.";
              exit();
          }
        }
        else
        {
            // render view
            View::renderTemplate('Error/index.html', [
                'vote_error_msg' => 'true'
            ]);
        }
    }



    /**
     * decreases value of comment_voteup field for referenced post & returns user to same page
     *
     * @return Boolean
     */
    public function voteDown()
    {
        // check if logged in
        if($_SESSION['user_id'])
        {
            // get string query data
            $comment_id = ( isset($_REQUEST['comment_id']) ) ? filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT): '';
            $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT): '';

            // decrement comment_voteup
            $result = Comment::voteDown($comment_id);

            // store user ID in variable
            $user_id = $_SESSION['user_id'];

            if($result)
            {
                header("Location: /home/get-post?post_id=$post_id&user_id=$user_id");
                exit();
            }
            else
            {
                echo "Error. Unable to execute query.";
                exit();
            }
        }
        else
        {
            // render view
            View::renderTemplate('Error/index.html', [
                'vote_error_msg' => 'true'
            ]);
        }
    }



    /**
     * edit stored comment
     *
     * @return view
     */
    public function editComment()
    {
        // get string query data
        $comment_id = ( isset($_REQUEST['comment_id']) ) ? filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $user_id = ( isset($_REQUEST['user_id']) ) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // get comment text
        $comment = Comment::getComment($comment_id);

        // // test
        // echo '<pre>';
        // print_r($comment);
        // echo '</pre>';
        // exit();

        // store comment author user ID in variable
        $comment_author  = $comment->comment_user_id;

        // check if comment author is the same user as user trying to edit comment
        if($comment->comment_user_id == $user_id )
        {
            // view
            View::renderTemplate('Home/edit-comment.html', [
                'comment' => $comment
            ]);
        }
        else
        {
            // view
            View::renderTemplate('Error/index.html', [
                'comment' => $comment,
                'editcomment' => 'true'
            ]);
        }
    }



    /**
     * updates comment in comments table for referenced comment
     *
     * @return Boolean
     */
    public function updateComment()
    {
        // get string query data
        $comment_id = ( isset($_REQUEST['comment_id']) ) ? filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $user_id = ( isset($_REQUEST['user_id']) ) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // update comment in comments table
        $result = Comment::updateComment($comment_id);

        if($result)
        {
            header("Location: /home/get-post?post_id=$post_id&amp;user_id=$user_id");
            exit();
        }
        else
        {
            echo "Error updating comment in database.";
            exit();
        }
    }









    public function submitReplyComment()
    {
        if(!$_SESSION['user_id'])
        {
            // echo "Not logged in."; exit();

            // render view
            View::renderTemplate('Error/index.html', [
                'reply_error_msg' => 'true'
            ]);
        }
        else
        {
            // retrieve query string variables
            $comment_id = (isset($_REQUEST['comment_id'])) ?  filter_var($_REQUEST['comment_id'], FILTER_SANITIZE_NUMBER_INT) : '';
            $post_id = (isset($_REQUEST['post_id'])) ?  filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';
            $user_id = (isset($_REQUEST['user_id'])) ?  filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
            $comment_user_id = (isset($_REQUEST['comment_user_id'])) ?  filter_var($_REQUEST['comment_user_id'], FILTER_SANITIZE_NUMBER_INT) : '';

            // test
            echo '<pre>';
            print_r($_REQUEST);
            echo '</pre>';
            // exit();

            // get user data for user who wrote parent comment
            $parent_user = User::getUser($comment_user_id);

            // get user data for replying user (child)
            $child_user = User::getUser($user_id);

            // get post data
            $post = Post::getPost($post_id);

            // insert reply into `comments` table - pass comment ID (parentID), user data & post data
            $results = Comment::submitReplyComment($comment_id, $post, $child_user);

            // test
            echo '<pre>';
            print_r($results);
            echo '</pre>';
            exit();

            if($results['result'])
            {
                // get post author data for mail
                $post_author = User::getAuthor($post->post_author);
            }

            // create array for mail data
            $mail_data = [
                'child_user'        => $user,
                'post'              => $post,
                'child_comment_id'  => $results['reply_id'], // last reply
                'post_author'       => $post_author,
                'parent_comment_id' => $comment_id,
                'parent_user'       => $parent_user
            ];

            if($results['result'])
            {
                // send email to reply author and original comment author
                $result = Mail::notifyCommentAuthorAboutReply($post_author, $post, $parent_user, $child_user);
            }
        }
    }





    public function submitContact()
    {
        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
           return false;
           exit();
        }

        unset($_SESSION['contacterror']);

        // set gate-keeper
        $okay = true;

        // retrieve data
        $first_name = (isset($_REQUEST['first_name'])) ?  filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = (isset($_REQUEST['last_name'])) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $telephone = (isset($_REQUEST['telephone'])) ?  filter_var($_REQUEST['telephone'], FILTER_SANITIZE_NUMBER_INT) : '';
        $email = (isset($_REQUEST['email'])) ?  filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $message = (isset($_REQUEST['message'])) ?  filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING) : '';

        // check for empty fields
        if($first_name === '' || $last_name === '' || $telephone === '' || $email === '' || $message === '')
        {
            $_SESSION['contacterror'] = "All fields are required";
            $okay = false;
            header("Location: /contact");
            exit();
        }

        if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
        {
            $_SESSION['contacterror'] = "Please enter valid email address";
            $okay = false;
            header("Location: /contact");
            exit();
        }

        // test
        // echo $first_name . "<br>";
        // echo $last_name . "<br>";
        // echo $telephone . "<br>";
        // echo $email . "<br>";
        // echo $message . "<br>";
        // exit();

        if($okay)
        {
            // call mailContactFormData method of Mail class & store boolean in $result
            $result = Mail::mailContactFormData($first_name, $last_name, $telephone, $email, $message);

            // if successful display success message in view
            if ($result)
            {
                // display success message in view
                $message = "Your information was sent. We will contact you as soon
                as possible.";

                View::renderTemplate('Success/index.html', [
                    'first_name'  => $first_name,
                    'last_name'   => $last_name,
                    'message'     => $message,
                    'contactform' => 'true'
                ]);
            }
            else
            {
                echo 'Mailer error';
                exit();
            }
        }
        else
        {
            $_SESSION['contacterror'] = "Please check reCAPTCHA box before submitting form.";
            header("Location: /contact");
            exit();
        }
    }


}
