<?php

namespace App\Models;

use PDO;

/**
 * Comment model
 */
class Comment extends \Core\Model
{

    /**
     * get categories
     *
     * @return Object The categories
     */
    public static function getComments($post_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM comments
                    WHERE comment_approved = :comment_approved
                    AND comment_post_id = :comment_post_id
                    ORDER BY comment_vote_count DESC, comment_date DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_post_id'  => $post_id,
                ':comment_approved' => 1
            ];
            $stmt->execute($parameters);
            $comments = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $comments;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * inserts comment into comments table by post ID
     * @param  Integer    $post_id    The post ID
     * @param  Integer    $user_id    The ID of user submitting comment
     * @return Boolean
     */
    public static function submitPostComment($post_id, $user)
    {
        // retrieve post data from form
        $comment_author = (isset($_REQUEST['user_full_name'])) ? filter_var($_REQUEST['user_full_name'], FILTER_SANITIZE_STRING) : '';
        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_STRING) : '';
        $comment_author_url = (isset($_REQUEST['comment_author_url'])) ? filter_var($_REQUEST['comment_author_url'], FILTER_SANITIZE_URL) : '';
        $comment = (isset($_REQUEST['post_comment'])) ? filter_var($_REQUEST['post_comment'], FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH) : '';

        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
           return false;
           exit();
        }

        // create token for notification email
        $comment_token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

        // create new date object & set time zone
        $date = new \DateTime();
        $zone = new \DateTimeZone("America/New_York");
        $date = new \DateTime();
        $date->setTimezone($zone);

        $now = $date->format('Y-m-d H:i:s');

        // store user full name in variable
        $user_full_name = $user->user_firstname . ' ' . $user->user_lastname;

        // store user login ip in variable
        $comment_ip = $_SESSION['user_login_ip'];

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        //
        // echo '$post_id => ' . $post_id . '<br>';
        // echo '$comment_author => ' . $comment_author . '<br>';
        // echo '$email => ' . $email . '<br>';
        // echo '$comment_author_url => ' . $comment_author_url . '<br>';
        // echo '$_SESSION[\'user_login_ip\'] => ' . $comment_ip. '<br>';
        // echo '$comment => ' . $comment . '<br>';
        // echo '$user_id => ' . $user_id . '<br>';
        // echo '$comment_token => ' . $comment_token;
        // exit();

        // if JavaScript off
        if($comment == '')
        {
            echo "Error. Comment text missing.";
            exit();
        }

        // submit data
        try
        {
            // establish DB connection
            $db = static::getDB();

            /* insert data into fields for parent comment */
            $sql = "INSERT INTO comments SET
                    comment_post_id       = :comment_post_id,
                    comment_user_id       = :comment_user_id,
                    comment_author        = :comment_author,
                    comment_author_email  = :comment_author_email,
                    comment_author_url    = :comment_author_url,
                    comment_date          = :comment_date,
                    comment_content       = :comment_content,
                    comment_approved      = :comment_approved,
                    comment_type          = :comment_type,
                    comment_parent        = :comment_parent,
                    comment_token         = :comment_token";
                    // comment_ip            = :comment_ip";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_post_id'       => $post_id,
                ':comment_user_id'       => $user->user_id,
                ':comment_author'        => $user_full_name,
                ':comment_author_email'  => $email,
                ':comment_author_url'    => $comment_author_url,
                ':comment_date'          => $now,
                ':comment_content'       => $comment,
                ':comment_approved'      => 0,
                ':comment_type'          => 'parent',
                ':comment_parent'        => null,
                ':comment_token'         => $comment_token,
                // ':comment_ip '           => $comment_ip
            ];
            $result = $stmt->execute($parameters);

            // Get ID of last insert (comments.comment_id)
            $comment_id = $db->lastInsertId();  // ID of new comment for this post

            $results = [
                'result'          => $result,
                'comment'         => $comment,
                'comment_author'  => $user_full_name,
                'comment_id'      => $comment_id,
                'comment_token'   => $comment_token,
                'comment_user_id' => $user->user_id
            ];

            return $results;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * updates comment into comments table by comment ID
     *
     * @param  Integer    $post_id    The post ID
     * @param  Integer    $user_id    The ID of user submitting comment
     * @return Boolean
     */
    public static function updateComment($comment_id)
    {
        // retrieve required post data from form
        $comment = (isset($_REQUEST['post_comment'])) ? filter_var($_REQUEST['post_comment'], FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
           return false;
           exit();
        }

        // if JavaScript off
        if($comment == '')
        {
            echo "Error. Comment text missing.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE comments SET
                    comment_content = :comment_content
                    WHERE comment_id = :comment_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_content' => $comment,
                ':comment_id'      => $comment_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates value of comments.comment_approved
     *
     * @param  Integer    $comment_id     The comment ID
     * @param  String     $comment_token  Unique ID for security
     * @return Boolean
     */
    public static function approveComment($comment_id, $comment_token)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO comments SET
                    comment_approved = :comment_approved
                    WHERE comment_id = :comment_id
                    AND comment_token = :comment_token";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_approved' => 1,
                ':comment_id'       => $comment_id,
                ':comment_token'    => $comment_token
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * increments value of comment_vote_count by one
     *
     * @param  Integer    $comment_id     The comment ID
     * @return Boolean
     */
    public static function voteUp($comment_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE comments SET
                    comment_vote_count = comment_vote_count + 1
                    WHERE comment_id = :comment_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_id' => $comment_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }



    /**
     * decrements value of comment_vote_count by one
     *
     * @param  Integer    $comment_id     The comment ID
     * @return Boolean
     */
    public static function voteDown($comment_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE comments SET
                    comment_vote_count = comment_vote_count - 1
                    WHERE comment_id = :comment_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_id' => $comment_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }




    public static function getComment($comment_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM comments
                    WHERE comment_id = :comment_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_id' => $comment_id
            ];
            $stmt->execute($parameters);
            $comment = $stmt->fetch(PDO::FETCH_OBJ);

            return $comment;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }









    /**
     * inserts comment into comments table by post ID
     *
     * @param  Integer    $comment_id     The comment ID
     * @param  Integer    $post_id        The post ID
     * @param  Integer    $user_id        The ID of user submitting comment
     * @return Boolean
     */
    public static function submitReplyComment($comment_id, $post, $child_user)
    {
        // retrieve post data from form
        $comment = (isset($_REQUEST['reply_comment'])) ? filter_var($_REQUEST['reply_comment'], FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH) : '';

        // check honeypot for robot content
        $honeypot = filter_var($_REQUEST['honeypot'], FILTER_SANITIZE_STRING);

        if($honeypot != '')
        {
           return false;
           exit();
        }

        // create token for notification email
        $comment_token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

        // create new date object & set time zone
        $date = new \DateTime();
        $zone = new \DateTimeZone("America/New_York");
        $date = new \DateTime();
        $date->setTimezone($zone);

        $now = $date->format('Y-m-d H:i:s');

        // store user login ip in variable
        $comment_ip = $_SESSION['user_login_ip'];

        // if JavaScript off
        if($comment == '')
        {
            echo "Error. Reply text missing.";
            exit();
        }

        // submit data
        try
        {
            // establish DB connection
            $db = static::getDB();

            /* insert data into fields for parent comment */
            $sql = "INSERT INTO comments SET
                    comment_post_id       = :comment_post_id,
                    comment_user_id       = :comment_user_id,
                    comment_author        = :comment_author,
                    comment_author_email  = :comment_author_email,
                    comment_author_url    = :comment_author_url,
                    comment_date          = :comment_date,
                    comment_content       = :comment_content,
                    comment_approved      = :comment_approved,
                    comment_type          = :comment_type,
                    comment_parent        = :comment_parent,
                    comment_token         = :comment_token";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':comment_post_id'       => $post->post_id,
                ':comment_user_id'       => $child_user->user_id,
                ':comment_author'        => $child_user->user_display_name,
                ':comment_author_email'  => $child_user->user_email,
                ':comment_author_url'    => null,
                ':comment_date'          => $now,
                ':comment_content'       => $comment,
                ':comment_approved'      => 1,
                ':comment_type'          => 'reply',
                ':comment_parent'        => $comment_id,
                ':comment_token'         => $comment_token
            ];
            $result = $stmt->execute($parameters);

            // Get ID of last insert (comments.comment_id)
            $reply_id = $db->lastInsertId();  // ID of new comment for this post

            // create array to pass data back to controller
            $results = [
                'result'          => $result,
                'comment'         => $comment,
                'child_author'    => $child_user->user_display_name,
                'comment_id'      => $comment_id,
                'comment_token'   => $comment_token,
                'child_user_id'   => $child_user->user_id,
                'post_id'         => $post->post_id,
                'reply_id'        => $reply_id
            ];

            return $results;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


}
