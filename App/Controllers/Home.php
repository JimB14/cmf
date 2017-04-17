<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Post;


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
        // echo "Hello from the index method in the Home controller!<br><br>"; exit();

        // get all posts
        // $posts = Post::getPosts($limit=5,$orderby='posts.post_date DESC');

        $pagetitle = "";

        $subtitle = "";

        View::renderTemplate('Home/index.html', [
            //'posts'     => $posts,
            'pagetitle' => $pagetitle,
            'subtitle'  => $subtitle,
            'homeindex' => 'active'
        ]);
    }


}
