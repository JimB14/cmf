<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * About controller
 *
 * PHP version 7.0
 */
class Authors extends \Core\Controller
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
     * display view
     *
     * @return
     */
    public function indexAction()
    {
        // create alphabet array
        $alphabet = range('A', 'Z');

        // store author status in variable
        $user_status = '5';

        // get authors
        $authors = User::getAuthors($user_status);

        // render view
        View::renderTemplate('Authors/index.html', [
            'alphabet'      => $alphabet,
            'authors'       => $authors,
            'authorsindex'  => 'active'
        ]);
    }



    /**
     * display view
     *
     * @return
     */
    public function getAuthorsAction()
    {
        // get letter to be searched
        $letter = $this->route_params['author'];

        // create alphabet array
        $alphabet = range('A', 'Z');

        // get authors
        $authors = User::getAuthorsByLastNameInitial($letter);

        // render view
        View::renderTemplate('Authors/index.html', [
            'alphabet'      => $alphabet,
            'authors'       => $authors,
            'letter'        => $letter,
            'authorsindex'  => 'active'
        ]);
    }

}
