<?php

namespace App\Models;

use PDO;

/**
 * Aphorism model
 *
 * PHP version 7.0
 */
class Aphorism extends \Core\Model
{
    /**
     * retrieve all testimonials
     * @return arrat  The testimonials
     */
    public static function getAllAphorisms($orderby)
    {
        if($orderby != null)
        {
            $orderby = 'ORDER BY ' . $orderby;
        }
        else
        {
            $orderby = 'ORDER BY aphorism_lastname';
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM aphorisms
                    $orderby";
            $stmt = $db->query($sql);
            $aphorisms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // return object to Controller
            return $aphorisms;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage;
            exit();
        }
    }

}
