<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Post;
use \App\Models\Category;
use \App\Models\User;
use \App\Models\Comment;


/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // echo "(before) ";
        // return false;  // prevents originally called method from executing
    }


    protected function after()
    {
        //echo " (after)";

    }


    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        // get all posts
        $posts = Post::getPosts();

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        // recent posts
        $recent_posts = Post::getRecentPosts($limit=7);

        // get all categories
        $categories = Category::getCategories();

        // get authors
        $authors = User::getAuthors($user_status=5);

        // test
        // echo '<pre>';
        // print_r($authors);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Home/index.html', [
            'posts'       => $posts,
            'recentposts' => $recent_posts,
            'categories'  => $categories,
            'authors'     => $authors,
            'homeindex'   => 'active'
        ]);
    }


    /**
     * Get post data by post ID
     *
     * @return void
     */
    public function getPostAction()
    {
        // get string query data
        $post_id = ( isset($_REQUEST['post_id']) ) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $user_id = ( isset($_REQUEST['user_id']) ) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // get post data
        $post = Post::getPost($post_id);

        // get related posts by same author, excluding $post_id
        $author_posts = Post::getOtherAuthorPosts($post->post_author, $post_id, $limit=3);

        // store tags in variable
        $tags = $post->post_tags;

        /* Resource Tajinder Singh: http://stackoverflow.com/questions/4898800/php-regex-remove-space-after-every-comma-in-string  */
        while(strpos($tags, ', ') != false)
        {
            $tags = str_replace(', ', ',', $tags);
        }

        // Explode string into array for use below
        $tags_array = explode(",", $tags);

        // test
        // echo '<pre>';
        // print_r($tags_array);
        // echo '</pre>';
        // exit();

        // get comments
        $comments = Comment::getComments($post_id);

        // test
        // echo '<pre>';
        // print_r($comments);
        // echo '</pre>';
        // exit();

        // render view with user data
        if($user_id != '')
        {
            // get logged in user's data
            if($_SESSION['user_id'])
            {
                $user = User::getUser($_SESSION['user_id']);
            }

            View::renderTemplate('Home/single.html', [
                'post'          => $post,
                'comments'      => $comments,
                'tags'          => $tags_array,
                'user'          => $user,
                'author_posts'  => $author_posts,
                'homeindex'     => 'active'
            ]);
        }
        else
        // render view without user data
        {
            View::renderTemplate('Home/single.html', [
                'post'          => $post,
                'comments'      => $comments,
                'tags'          => $tags_array,
                'author_posts'  => $author_posts,
                'homeindex'     => 'active'
            ]);
        }

    }




    /**
     * Get posts by users.user_id
     *
     * @return Object The posts
     */
    public function getAuthorPostsAction()
    {
        // get post ID
        $post_author = $this->route_params['id'];

        // get post data
        $posts = Post::getAuthorPosts($post_author, $limit=5);

        // get all published posts
        $all_published_posts = Post::getAllPublishedAuthorPosts($post_author);

        // get author data
        $author = User::getAuthor($post_author);

        // test
        // echo '<pre>';
        // print_r($author);
        // echo '</pre>';
        // exit();

        // store author display name in variable
        $author_name = $author->user_display_name;

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Home/author.html', [
            'posts'       => $posts,
            'author'      => $author,
            'author_name' => $author_name,
            'allposts'    => $all_published_posts,
            'homeindex'   => 'active'
        ]);
    }



    /**
     * Get posts by users.user_id
     *
     * @return Object The posts
     */
    public function getPostsByAuthorAction()
    {
        // get post author name
        $post_author = $this->route_params['author'];

        // get post author ID
        $post_author_id = $this->route_params['id'];

        // test
        // echo $post_author .'<br>';
        // echo $post_author_id;
        // exit();

        // get post data
        $posts = Post::getAuthorPosts($post_author_id, $limit=5);

        // get all published posts
        $all_published_posts = Post::getAllPublishedAuthorPosts($post_author_id);

        // get author data
        $author = User::getAuthor($post_author_id);

        // test
        // echo '<pre>';
        // print_r($author);
        // echo '</pre>';
        // exit();

        // store author display name in variable
        $author_name = $author->user_display_name;

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Home/author.html', [
            'posts'       => $posts,
            'author'      => $author,
            'author_name' => $author_name,
            'allposts'    => $all_published_posts,
            'homeindex'   => 'active'
        ]);
    }




    /**
     * Get posts by users.user_id
     *
     * @return Object The posts
     */
    public function getSpecificAuthorPostsAction()
    {
        // retrieve query string variables
        $author_id = (isset($_REQUEST['author_id'])) ? filter_var($_REQUEST['author_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $offset = (isset($_REQUEST['offset'])) ? filter_var($_REQUEST['offset'], FILTER_SANITIZE_NUMBER_INT) : '';
        $limit = (isset($_REQUEST['limit'])) ? filter_var($_REQUEST['limit'], FILTER_SANITIZE_NUMBER_INT) : '';

        // get post data
        $posts = Post::getSpecificAuthorPosts($author_id, $offset, $limit);

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        // get all published posts
        $all_published_posts = Post::getAllPublishedAuthorPosts($author_id);

        // get author data
        $author = User::getAuthor($author_id);

        // store author display name in variable
        $author_name = $author->user_display_name;

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Home/author.html', [
            'posts'       => $posts,
            'author'      => $author,
            'author_name' => $author_name,
            'allposts'    => $all_published_posts,
            'homeindex'   => 'active',
            'offset'      => $offset
        ]);
    }



    /**
     * Display posts by categories.category_id
     *
     * @return Object The posts
     */
    public function getSpecificCategoryPostsAction()
    {
        // retrieve query string variables
        $category_id = (isset($_REQUEST['category_id'])) ? filter_var($_REQUEST['category_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $offset = (isset($_REQUEST['offset'])) ? filter_var($_REQUEST['offset'], FILTER_SANITIZE_NUMBER_INT) : '';
        $limit = (isset($_REQUEST['limit'])) ? filter_var($_REQUEST['limit'], FILTER_SANITIZE_NUMBER_INT) : '';

        // get set of posts in referenced category according to offset & limit values
        $category_posts = Post::getSpecificCategoryPosts($category_id, $offset, $limit);

        // test
        // echo '<pre>';
        // print_r($category_posts);
        // echo '</pre>';
        // exit();

        // get all published posts in referenced category
        $all_published_posts = Post::getAllPublishedPostsByCategory($category_id);

        // get category data
        $category = Category::getCategoryById($category_id);

        // assign category title to variable
        $category_title = $category->category_title;

        // test
        // echo '<pre>';
        // print_r($category);
        // echo '</pre>';
        // exit();

        // test
        // echo '<pre>';
        // print_r($all_published_posts);
        // echo '</pre>';
        // echo $category->category_title.'<br>';
        // exit();

        View::renderTemplate('Home/category.html', [
            'posts'           => $category_posts,
            'category_id'     => $category_id,
            'category_title'  => $category_title,
            'allposts'        => $all_published_posts,
            'homeindex'       => 'active',
            'offset'          => $offset
        ]);
    }



    /**
     * Get posts by category_id
     *
     * @return Object The posts
     */
    public function getPostsByCategoryAction()
    {
        // get post ID
        $category_id = $this->route_params['id'];

        // get category by ID
        $category = Category::getCategoryById($category_id);

        // assign category title to variable
        $category_title = $category->category_title;

        // get post data
        $posts = Post::getPostsByCategory($category_id);

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        // get all published posts
        $all_published_posts = Post::getAllPublishedPostsByCategory($category_id);

        // test
        // echo '<pre>';
        // print_r($posts);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Home/category.html', [
            'posts'           => $posts,
            'category_id'     => $category_id,
            'category_title'  => $category_title,
            'allposts'        => $all_published_posts,
            'homeindex'       => 'active'
        ]);
    }

}
