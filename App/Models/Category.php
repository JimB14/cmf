<?php

namespace App\Models;

use PDO;

/**
 * Category model
 */
class Category extends \Core\Model
{

    /**
     * get categories
     *
     * @return Object The categories
     */
    public static function getCategories()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM categories
                    ORDER BY category_title";
            $stmt = $db->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $categories;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * gets category by ID
     *
     * @param  integer $category The category ID
     * @return string           The category name
     */
    public static function getCategoryById($category_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM categories
                    WHERE category_id = :category_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':category_id' => $category_id
            ];
            $stmt->execute($parameters);
            $category = $stmt->fetch(PDO::FETCH_OBJ);

            // return value to Listing Controller
            return $category;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * gets business category name
     *
     * @param  integer $subcategory The sub category ID
     * @return string           The sub category name
     */
    public static function getSubCategoryName($subcategory)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT sub_cat_name FROM sub_category WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $subcategory
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            $subcategory_name = $result->sub_cat_name;

            // return value to Listing Controller
            return $subcategory_name;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
