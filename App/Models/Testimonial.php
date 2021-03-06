<?php

namespace App\Models;

use PDO;

/**
 * Testimonial model
 *
 * PHP version 7.0
 */
class Testimonial extends \Core\Model
{
    /**
     * retrieve all testimonials
     * @return arrat  The testimonials
     */
    public static function getAllTestimonials()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT testimonials.*, users.*
                    FROM testimonials
                    JOIN users ON
                    testimonials.user_id = users.user_id
                    WHERE testimonials.display = 1
                    ORDER BY testimonials.created_at DESC";
            $stmt = $db->query($sql);
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // return object to Controller
            return $testimonials;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage;
            exit();
        }
    }



    /**
     * Adds new testimonial to database
     */
    public static function addNewTestimonial()
    {
        unset($_SESSION['addtestimonialerror']);

        // set gatekeeper to true
        $okay = true;

        // retrieve form data
        $title = ( isset($_REQUEST['title']) ) ? filter_var($_REQUEST['title'], FILTER_SANITIZE_STRING) : "";
        $testimonial = ( isset($_REQUEST['testimonial']) ) ? $_REQUEST['testimonial'] : "";
        $name = ( isset($_REQUEST['testimonial_full_name']) ) ? $_REQUEST['testimonial_full_name'] : "";

        // echo $testimonial; exit();

        // validate data
        if($title == '' || filter_var($title, FILTER_SANITIZE_STRING === false))
        {
            $_SESSION['addtestimonialerror'] = 'Please enter a valid title';
            $okay = false;
            header("Location: /testimonials/add-testimonial");
            exit();
        }

        if($testimonial == '' || filter_var($testimonial, FILTER_SANITIZE_STRING === false))
        {
            $_SESSION['addtestimonialerror'] = 'Please enter a valid testimonial';
            $okay = false;
            header("Location: /testimonials/add-testimonial");
            exit();
        }


        if($okay)
        {
            try
            {
                // establish db connection
                $db = static::getDB();

                // create token for notification email
                $token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

                $sql = "INSERT INTO testimonials (title, testimonial, user_id, token)
                        VALUES (:title, :testimonial, :user_id, :token)";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':title'        => $title,
                    ':testimonial'  => $testimonial,
                    ':user_id'      => $_SESSION['user_id'],
                    ':token'        => $token
                ];

                $result = $stmt->execute($parameters);

                // get id of new testimonial
                $id = $db->lastInsertId();

                // return array of data to testimonials controller
                $results = [
                    'result'      => $result,
                    'id'          => $id,
                    'token'       => $token,
                    'name'        => $name,
                    'title'       => $title,
                    'testimonial' => $testimonial
                ];

                // return to Testimonials Controller
                return $results;
            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
        else
        {
            $_SESSION['addtestimonialerror'] = 'Unable to add testimonial. Please try again.';
            $okay = false;
            header("Location: /testimonials/add-testimonial");
            exit();
        }
    }




    /**
     * Updates display field of approved testimonial
     */
    public static function setTestimonialToDisplay($id, $token)
    {
        // initiate gatekeeper
        $okay = true;

        if($token === '' || $id === '')
        {
            echo "Error. Variables are null";
            $okay = false;
            exit();
        }

        if(filter_var($token, FILTER_SANITIZE_STRING === false) || filter_var($id, FILTER_SANITIZE_NUMBER_INT === false))
        {
            echo "Error found in variables";
            $okay = false;
            exit();
        }

        // echo $token . "<br>";
        // echo $id . "<br>";
        // exit();

        if($okay)
        {
            // update display field if match found
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE testimonials SET display = 1
                        WHERE id = :id AND token = :token";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id,
                    ':token' => $token
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
}
