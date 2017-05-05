<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Page;
use \App\Mail;
use \App\Config;
use \App\Models\Recaptcha;

/**
 * About controller
 *
 * PHP version 7.0
 */
class About extends \Core\Controller
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



    public function indexAction()
    {
        View::renderTemplate('About/index.html', [
           'aboutindex'  => 'active'
        ]);
    }

}
