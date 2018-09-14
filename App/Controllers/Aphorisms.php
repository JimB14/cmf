<?php

namespace App\Controllers;


use \Core\View;
use \App\Models\Aphorism;
use \App\Models\User;
use \App\Mail;

/**
 * Aphorisms controller
 *
 * PHP version 7.0
 */
class Aphorisms extends \Core\Controller
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
     * displays aphorisms
     *
     * @return object The testimonials
     */
    public function indexAction()
    {
        // get all testimonials
        $aphorisms = Aphorism::getAllAphorisms($orderby=null);

        // test
        // echo '<pre>';
        // print_r($aphorisms);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Aphorisms/index.html', [
            'aphorisms'      => $aphorisms,
            'aphorismsindex' => 'active'
        ]);
    }



    /**
     * displays sorted aphorisms
     *
     * @return object The testimonials
     */
    public function sortAscAction()
    {
        // get all testimonials
        $aphorisms = Aphorism::getAllAphorisms($orderby='aphorism_lastname ASC');

        // test
        // echo '<pre>';
        // print_r($aphorisms);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Aphorisms/index.html', [
            'aphorisms'      => $aphorisms,
            'aphorismsindex' => 'active'
        ]);
    }



    /**
     * displays sorted aphorisms
     *
     * @return object The testimonials
     */
    public function sortDescAction()
    {
        // get all testimonials
        $aphorisms = Aphorism::getAllAphorisms($orderby='aphorism_lastname DESC');

        // test
        // echo '<pre>';
        // print_r($aphorisms);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Aphorisms/index.html', [
            'aphorisms'      => $aphorisms,
            'aphorismsindex' => 'active'
        ]);
    }

}
