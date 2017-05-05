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
class Search extends \Core\Controller
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
     * returns with posts with keyword match
     *
     * @return View
     */
    public function indexAction()
    {
        // get user ID
        $user_id = $this->route_params['id'];

        // get post data
        $results = Post::searchPosts($user_id);

        // test
        // echo '<pre>';
        // print_r($results);
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
            'posts'       => $results['posts'],
            'recentposts' => $recent_posts,
            'categories'  => $categories,
            'authors'     => $authors,
            'searchtext'  => $results['searchtext'],
            'homeindex'   => 'active'
        ]);
    }

}
