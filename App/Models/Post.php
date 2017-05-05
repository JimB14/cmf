<?php

namespace App\Models;

use PDO;

/**
 * Post model
 */
class Post extends \Core\Model
{

    /**
     * gets all posts from posts table
     *
     * @return Object  The posts
     */
    public static function getPosts()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    AND posts.post_status = 'publish'
                    ORDER BY posts.post_date DESC";
            $stmt = $db->query($sql);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets x number of posts from posts table
     *
     * @return Object  The posts
     */
    public static function getRecentPosts($limit)
    {
        if($limit != null)
        {
            $limit = 'Limit ' . $limit;
        }
        else
        {
            $limit = $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    AND posts.post_status = 'publish'
                    ORDER BY posts.post_date DESC
                    $limit";
            $stmt = $db->query($sql);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * get post by Post ID
     *
     * @return Object  The post
     */
    public static function getPost($post_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN users ON
                    posts.post_author = users.user_id
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    WHERE posts.post_id = :post_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_id'  => $post_id
            ];
            $stmt->execute($parameters);
            $post = $stmt->fetch(PDO::FETCH_OBJ);

            return $post;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * gets all posts by author
     *
     * @return Object  The posts
     */
    public static function getAuthorPosts($post_author, $limit)
    {
        if($limit != null)
        {
          $limit = 'Limit ' . $limit;
        }

        // test
        // echo '<br> post_author: ' . $post_author;
        // echo '<br> post_id: ' . $post_id;
        // echo '<br> limit: ' . $limit;
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_author = :post_author
                    AND posts.post_status = :post_status
                    ORDER BY posts.post_date DESC
                    $limit";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author'  => $post_author,
                ':post_status'  => 'publish'
            ];
            $stmt->execute($parameters);

            $author_posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $author_posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }




    /**
     * gets specified posts by author
     *
     * @return Object  The posts
     */
    public static function getSpecificAuthorPosts($post_author, $offset, $limit)
    {
        // format for MySQL query
        $offset = 'OFFSET '. $offset;
        $limit = 'LIMIT ' . $limit;

        // test
        // echo '<br> post_author: ' . $post_author;
        // echo '<br> offset: ' . $offset;
        // echo '<br> limit: ' . $limit;
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_author = :post_author
                    AND posts.post_status = :post_status
                    ORDER BY posts.post_date DESC
                    $limit $offset";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author'  => $post_author,
                ':post_status'  => 'publish'
            ];
            $stmt->execute($parameters);

            $author_posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $author_posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets specified posts by category ID
     *
     * @return Object  The posts
     */
    public static function getSpecificCategoryPosts($category_id, $offset, $limit)
    {
        // format for MySQL query
        $offset = 'OFFSET '. $offset;
        $limit = 'LIMIT ' . $limit;

        // test
        // echo '<br> category_id: ' . $category_id;
        // echo '<br> offset: ' . $offset;
        // echo '<br> limit: ' . $limit;
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT posts.*, categories.*, users.*
                    FROM posts
                    INNER JOIN categories ON
                    posts.post_category = categories.category_id
                    INNER JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_category = :post_category
                    AND posts.post_status = :post_status
                    ORDER BY posts.post_date DESC
                    $limit $offset";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_category'  => $category_id,
                ':post_status'    => 'publish'
            ];
            $stmt->execute($parameters);

            $category_posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $category_posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets all posts by author except post being viewed
     *
     * @return Object  The posts
     */
    public static function getOtherAuthorPosts($post_author, $post_id, $limit)
    {
        if($limit != null)
        {
          $limit = 'Limit ' . $limit;
        }

        // test
        // echo '<br> post_author: ' . $post_author;
        // echo '<br> post_id: ' . $post_id;
        // echo '<br> limit: ' . $limit;
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_author = :post_author
                    AND posts.post_status = :post_status
                    AND posts.post_id <> :post_id
                    ORDER BY posts.post_date DESC
                    $limit";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author'  => $post_author,
                ':post_status'  => 'publish',
                ':post_id'      => $post_id
            ];
            $stmt->execute($parameters);

            $author_posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $author_posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets all posts by author (draft & publish)
     *
     * @return Object  The posts
     */
    public static function getAllAuthorPosts($post_author)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_author = :post_author
                    ORDER BY posts.post_date DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author' => $post_author
            ];
            $stmt->execute($parameters);

            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets all posts by author where post_status == publish
     *
     * @return Object  The posts
     */
    public static function getAllPublishedAuthorPosts($post_author)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_author = :post_author
                    AND posts.post_status = :post_status
                    ORDER BY posts.post_date DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author'  => $post_author,
                ':post_status'  => 'publish'
            ];
            $stmt->execute($parameters);

            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * get post by Post Category ID
     *
     * @return Object  The post
     */
    public static function getPostsByCategory($category_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN users ON
                    posts.post_author = users.user_id
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    WHERE posts.post_category = :post_category
                    AND users.user_status = :user_status
                    AND posts.post_status = 'publish'
                    ORDER BY posts.post_date DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_category'  => $category_id,
                ':user_status'    => 5
            ];
            $stmt->execute($parameters);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }




    /**
     * gets all posts by category where post_status == publish
     *
     * @return Object  The posts
     */
    public static function getAllPublishedPostsByCategory($category_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT posts.*, categories.*, users.*
                    FROM posts
                    INNER JOIN categories ON
                    posts.post_category = categories.category_id
                    INNER JOIN users ON
                    posts.post_author = users.user_id
                    WHERE posts.post_category = :category_id
                    AND posts.post_status = :post_status
                    ORDER BY posts.post_date DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':category_id'  => $category_id,
                ':post_status'  => 'publish'
            ];
            $stmt->execute($parameters);

            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $posts;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets most recent post by author
     *
     * @return Object  The posts
     */
    public static function getLatestAuthorPost($post_author)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    JOIN users ON
                    posts.post_author = users.user_id
                    AND posts.post_status = 'publish'
                    WHERE posts.post_author = :post_author
                    ORDER BY post_date DESC
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_author' => $post_author
            ];
            $stmt->execute($parameters);

            $last_post = $stmt->fetch(PDO::FETCH_OBJ);

            return $last_post;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * Inserts new post into posts table
     * @param  String  $post_author   The author's display name
     * @return Boolean
     */
    public static function insertPost($post_author)
    {
        // retrieve post data
        $post_author_name = ( isset($_REQUEST['post_author']) ) ? filter_var($_REQUEST['post_author'], FILTER_SANITIZE_STRING) : "";
        $post_category = ( isset($_REQUEST['post_category']) ) ? filter_var($_REQUEST['post_category'], FILTER_SANITIZE_NUMBER_INT) : "";
        $post_title = ( isset($_REQUEST['post_title']) ) ? filter_var($_REQUEST['post_title'], FILTER_SANITIZE_STRING) : "";
        $post_content = ( isset($_REQUEST['post_content']) ) ? filter_var($_REQUEST['post_content'],FILTER_FLAG_STRIP_HIGH) : "";
        $post_tags = ( isset($_REQUEST['post_tags']) ) ? strtolower(filter_var($_REQUEST['post_tags'], FILTER_SANITIZE_STRING)) : "";
        $no_image = ( isset($_REQUEST['no_image']) ) ? filter_var($_REQUEST['no_image'], FILTER_SANITIZE_NUMBER_INT) : "";

        print_r($_REQUEST);
        // exit();

        // image is being uploaded
        if($no_image == '')
        {
            // Set configurations: assign target directory to variable
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_post_images/';

            if($_SERVER['SERVER_NAME'] != 'localhost')
            {
              // path for live server
              $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_post_images/';
            }
            else
            {
              // path for local machine
              $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_post_images/';
            }


            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['post_img']['name'];
            $file_tmp = $_FILES['post_img']['tmp_name'];
            $file_type = $_FILES['post_img']['type'];
            $file_size = $_FILES['post_img']['size'];
            $file_err_msg = $_FILES['post_img']['error'];

            // test
            echo '<br>'. $_FILES['post_img']['name'];
            echo '<br> tmp_name: '. $_FILES['post_img']['tmp_name'];
            echo '<br> type: '. $_FILES['post_img']['type'];
            echo '<br> size: '. $_FILES['post_img']['size'];
            echo '<br> error: '. $_FILES['post_img']['error'];
            // exit();

            /* - - - - - evaluate uploaded image  - - - - - - - */

            // method one - store image attributes in array
            // $size = getimagesize($_FILES['post_img']['tmp_name']);
            // for method 1: store image size (pixels) in corresponding variables
            // $image_width = $size[0];
            // $image_height = $size[1];

            // method 2: store first two image attributes from getimagesize() array, using list()
            list($image_width, $image_height) = getimagesize($_FILES['post_img']['tmp_name']);

            // calculate width/height ratio & store in variable
            $ratio = number_format($image_width/$image_height, 1);

            // test
            echo '<br>width: ' . $image_width . '<br>';
            echo 'height: ' . $image_height . '<br>';
            echo 'ratio: ' . $ratio . '<br><br>';
            exit();

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix for author & post specific image identification
            $prefix = $post_author.'-'.time(). '-';

            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Check if file already exists
            if ( file_exists($target_dir . $file_name) )
            {
                // if (file_exists($target_file))
                $upload_ok = 0;
                echo "Sorry, image file already exists. Please select a
                      different file or rename file and try again.";
                exit();
            }

            // Check if file size < 10 MB
            if($file_size > 10485760)   // 2097152 = 2MB; 1048576 = 1MB
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'File must be less than 10 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }
            // Check for any errors
            if($file_err_msg == 1)
            {
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 )
            {
                // Attach prefix to file name so server & database table match
                $file_name = $prefix . $file_name;

                 // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp_loc);
                    echo 'File not uploaded. Please try again.';
                    exit();
                }

                /*  - - - -   Image Re-sizing & Over-writing   - - - - - - -  */
                // resize image if ratio < 2.0
                if($image_width < 900)
                {
                    // message
                    echo "The image you are attempting to upload is less than 900px wide.
                          Please upload another image at least 900px wide.";
                    exit();
                }
                if($image_width >= 900 && $image_height >= 450)
                {
                    // include_once 'Library/image-resizing-to-scale.php';

                    include_once 'Library/image-resizing-ignoring-scale.php';

                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_post_images/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_post_images/$file_name";
                      $wmax = 900;
                      $hmax = 450;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_post_images/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_post_images/$file_name";
                      $wmax = 900;
                      $hmax = 450;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }
            else
            {
                echo 'File not uploaded. Please try again.';
                exit();
            }

            // Add post with photo
            try
            {
                // establish db connection
                $db = static::getDB();

                // create token for notification email
                $post_token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

                $sql = "INSERT INTO posts SET
                        post_category   = :post_category,
                        post_author     = :post_author,
                        post_img        = :post_img,
                        post_content    = :post_content,
                        post_title      = :post_title,
                        post_tags       = :post_tags,
                        post_token      = :post_token";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':post_category'  => $post_category,
                    ':post_author'    => $post_author,
                    ':post_img'       => $file_name,
                    ':post_content'   => $post_content,
                    ':post_title'     => $post_title,
                    ':post_tags'      => $post_tags,
                    ':post_token'     => $post_token
                ];
                $result = $stmt->execute($parameters);

                // Get ID of last insert (posts.post_id)
                $post_id = $db->lastInsertId();  // ID of new post for this author

                $results = [
                  'result'      => $result,
                  'post_id'     => $post_id,
                  'post_token'  => $post_token,
                  'image'       => 'has image'
                ];

                return $results;

            }
            catch (PDOException $e)
            {
                echo "Error inserting new agent data into database" . $e->getMessage();
                exit();
            }
        }
        // no image being uploaded
        else
        {
          // Add post without an image
          try
          {
              // establish db connection
              $db = static::getDB();

              // create token for notification email
              $post_token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

              $sql = "INSERT INTO posts SET
                      post_category   = :post_category,
                      post_author     = :post_author,
                      post_content    = :post_content,
                      post_title      = :post_title,
                      post_tags       = :post_tags,
                      post_token      = :post_token";
              $stmt = $db->prepare($sql);
              $parameters = [
                  ':post_category'  => $post_category,
                  ':post_author'    => $post_author,
                  ':post_content'   => $post_content,
                  ':post_title'     => $post_title,
                  ':post_tags'      => $post_tags,
                  ':post_token'     => $post_token
              ];
              $result = $stmt->execute($parameters);

              // Get ID of last insert (posts.post_id)
              $post_id = $db->lastInsertId();  // ID of new post for this author

              $results = [
                'result'      => $result,
                'post_id'     => $post_id,
                'post_token'  => $post_token,
                'image'       => 'needs image'
              ];

              return $results;

          }
          catch (PDOException $e)
          {
              echo "Error inserting new agent data into database" . $e->getMessage();
              exit();
          }
        }
    }




    /**
     * Updates referenced posts in posts table
     * @param  Integer    $post_id    The post ID
     * @return Boolean
     */
    public static function updatePost($post_id, $post_author)
    {
        // retrieve post data
        $post_author_name = ( isset($_REQUEST['post_author']) ) ? filter_var($_REQUEST['post_author'], FILTER_SANITIZE_STRING) : "";
        $post_category = ( isset($_REQUEST['post_category']) ) ? filter_var($_REQUEST['post_category'], FILTER_SANITIZE_NUMBER_INT) : "";
        $post_title = ( isset($_REQUEST['post_title']) ) ? filter_var($_REQUEST['post_title'], FILTER_SANITIZE_STRING) : "";
        $post_content = ( isset($_REQUEST['post_content']) ) ? $_REQUEST['post_content'] : "";
        $post_tags = ( isset($_REQUEST['post_tags']) ) ? strtolower(filter_var($_REQUEST['post_tags'], FILTER_SANITIZE_STRING)) : "";

        // test
        echo '<pre>';
        print_r($_REQUEST);
        echo '</pre>';
        // exit();

        // Check if post image was uploaded; if true, process
        if(!empty($_FILES['new_post_img']['tmp_name']))
        {
            // Set configurations: assign target directory to variable
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_post_images/';

            if($_SERVER['SERVER_NAME'] != 'localhost')
            {
              // path for live server
              $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_post_images/';
            }
            else
            {
              // path for local machine
              $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_post_images/';
            }

            // test
            echo '<br> target directory: ' . $target_dir;
            echo '<br> tmp_name: ' . $_FILES['new_post_img']['tmp_name'];
            // exit();


            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['new_post_img']['name'];
            $file_tmp = $_FILES['new_post_img']['tmp_name'];
            $file_type = $_FILES['new_post_img']['type'];
            $file_size = $_FILES['new_post_img']['size'];
            $file_err_msg = $_FILES['new_post_img']['error'];

            // test
            echo '<br> file name: '. $_FILES['new_post_img']['name'];
            echo '<br> tmp_name: '. $_FILES['new_post_img']['tmp_name'];
            echo '<br> type: '. $_FILES['new_post_img']['type'];
            echo '<br> size: '. $_FILES['new_post_img']['size'];
            echo '<br> error: '. $_FILES['new_post_img']['error'];
            // exit();

            /* - - - - - evaluate uploaded image  - - - - - - - */

            // method one - store image attributes in array
            //$size = getimagesize($_FILES['new_post_img']['tmp_name']);
            // for method 1: store image size (pixels) in corresponding variables
            // $image_width = $size[0];
            // $image_height = $size[1];

            // method 2: store first two image attributes from getimagesize() array, using list()
            list($image_width, $image_height) = getimagesize($_FILES['new_post_img']['tmp_name']);

            // calculate width/height ratio & store in variable
            $ratio = number_format($image_width/$image_height, 1);

            // test
            echo '<br> image width: '.$image_width;
            echo '<br> image height: '.$image_height;
            echo '<br> ratio (w:h): '.$ratio . '<br><br>';
            // exit();

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            /*  e.g. file name = filename.jpg
             *  $kaboom = [
             *   'filename',
             *   'jpg'
             *  ];
             */

            // test
            print_r($kaboom);
            // exit();

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix for author & post specific image identification
            $prefix = $post_author.'-'.time(). '-';

            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Check if file already exists
            if ( file_exists($target_dir . $file_name) )
            {
                // if (file_exists($target_file))
                $upload_ok = 0;
                echo "Sorry, image file already exists. Please select a
                      different file or rename file and try again.";
                exit();
            }

            // Check if file size < 10 MB
            if($file_size > 10485760)   // 2097152 = 2MB; 1048576 = 1MB
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'File must be less than 10 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }
            // Check for any errors
            if($file_err_msg == 1)
            {
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 )
            {
                // Attach prefix to file name so server & database table match
                $file_name = $prefix . $file_name;

                 // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp_loc);
                    echo 'File not uploaded. Please try again.';
                    exit();
                }


                /*  - - - -   Image Re-sizing & Over-writing   - - - - - - -  */
                // resize image if ratio < 2.0
                if($image_width < 900)
                {
                    // message
                    echo "The image you are attempting to upload is less than 900px wide.
                          Please upload another image at least 900px wide.";
                    exit();
                }
                if($image_width >= 900 && $image_height >= 450)
                {
                    // include_once 'Library/image-resizing-to-scale.php';

                    include_once 'Library/image-resizing-ignoring-scale.php';

                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_post_images/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_post_images/$file_name";
                      $wmax = 900;
                      $hmax = 450;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_post_images/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_post_images/$file_name";
                      $wmax = 900;
                      $hmax = 450;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }
            else
            {
                echo 'File not uploaded. Please try again.';
                exit();
            }

            // Update post with new photo
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE posts SET
                        post_category   = :post_category,
                        post_img        = :post_img,
                        post_content    = :post_content,
                        post_title      = :post_title,
                        post_tags       = :post_tags
                        WHERE post_id = :post_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':post_id'        => $post_id,
                    ':post_category'  => $post_category,
                    ':post_img'       => $file_name,
                    ':post_content'   => $post_content,
                    ':post_title'     => $post_title,
                    ':post_tags'      => $post_tags
                ];
                $result = $stmt->execute($parameters);

                return $result;

            }
            catch (PDOException $e)
            {
                echo "Error inserting new agent data into database" . $e->getMessage();
                exit();
            }
        }
        // no image being uploaded
        else
        {
            // Update post without an image
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE posts SET
                        post_category   = :post_category,
                        post_content    = :post_content,
                        post_title      = :post_title,
                        post_tags       = :post_tags
                        WHERE post_id = :post_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':post_id'        => $post_id,
                    ':post_category'  => $post_category,
                    ':post_content'   => $post_content,
                    ':post_title'     => $post_title,
                    ':post_tags'      => $post_tags
                ];
                $result = $stmt->execute($parameters);

                return $result;

            }
            catch (PDOException $e)
            {
                echo "Error inserting new agent data into database" . $e->getMessage();
                exit();
            }
        }
    }



    /**
     * deletes referenced post
     *
     * @return Boolean
     */
    public static function deletePost($post_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM posts
                    WHERE post_id = :post_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_id'  => $post_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }




    /**
     * get post by Post ID
     *
     * @return Object  The post
     */
    public static function getPostDraft($post_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM posts
                    JOIN users ON
                    posts.post_author = users.user_id
                    JOIN categories ON
                    posts.post_category = categories.category_id
                    WHERE posts.post_id = :post_id
                    AND posts.post_status = 'draft'";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_id'  => $post_id
            ];
            $stmt->execute($parameters);
            $post = $stmt->fetch(PDO::FETCH_OBJ);

            return $post;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * Updates post_status field of approved post to 'publish'
     */
    public static function setPostToDisplay($post_token, $post_id, $user_id)
    {
        // initiate gatekeeper
        $okay = true;

        if($post_token === '' || $post_id === '' || $user_id === '')
        {
            echo "Error. Variables are null";
            $okay = false;
            exit();
        }

        if(filter_var($post_token, FILTER_SANITIZE_STRING === false) || filter_var($id, FILTER_SANITIZE_STRING === false))
        {
            echo "Error found in variables";
            $okay = false;
            exit();
        }

        // test
        // echo $post_token . "<br>";
        // echo $post_id . "<br>";
        // echo $user_id;
        // exit();

        if($okay)
        {
            // update display field if match found
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE posts SET
                        post_status = 'publish'
                        WHERE post_id = :post_id
                        AND post_author = :post_author
                        AND post_token = :post_token";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':post_id'      => $post_id,
                    ':post_author'  => $user_id,
                    ':post_token'   => $post_token
                ];
                $result = $stmt->execute($parameters);

                return $result;
            }
            catch(PDOException $e)
            {
                echo "Error finding data match";
                exit();
            }
        }
    }


    /**
     * changes post status to publish or draft
     * @param  String   $post_status  The current status
     * @return Boolean
     */
    public static function togglePostStatus($post_id, $post_status)
    {
        if($post_status == 'draft')
        {
          $post_status = 'publish';
        }
        elseif($post_status == 'publish')
        {
            $post_status = "draft";
        }

        try
        {
            // establish database connection
            $db = static::getDB();

            $sql = "UPDATE posts SET
                    post_status = :post_status
                    WHERE post_id = :post_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_status'  => $post_status,
                ':post_id'      => $post_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves posts with text that matches keyword searched in post_content, post_title and post_author
     *
     * @return Object  The posts
     */
    public static function searchPosts()
    {
        // retrieve post data
        $searchtext = ( isset($_REQUEST['searchtext']) ) ? filter_var($_REQUEST['searchtext'], FILTER_SANITIZE_STRING) : "";

        if($searchtext == '')
        {
            echo '<script>';
            echo 'alert("The search field is empty.")';
            echo '</script>';

            echo '<script>';
            echo 'window.location.href="/"';
            echo '</script>';
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT posts.*, users.user_display_name, categories.*
                    FROM posts
                    INNER JOIN users ON
                    users.user_id = posts.post_author
                    INNER JOIN categories ON
                    posts.post_category = categories.category_id
                    WHERE posts.post_status = 'publish'
                    AND posts.post_content LIKE '$searchtext%'
                    OR posts.post_title LIKE '$searchtext%'
                    OR users.user_lastname LIKE '$searchtext%'
                    OR users.user_firstname LIKE '$searchtext%'
                    OR users.user_display_name LIKE '$searchtext%'
                    ORDER BY posts.post_date DESC";
            $stmt = $db->query($sql);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            // create array to return to controller
            $results = [
                'posts'       => $posts,
                'searchtext'  => $searchtext
            ];

            // return object to Search Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * increments posts.comment_count of referenced post by one
     *
     * @param  Integer    $post_id    The post ID
     * @return Boolean
     */
    public static function incrementCommentCount($post_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE posts SET
                    comment_count = comment_count + 1
                    WHERE post_id = :post_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':post_id' => $post_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



}
