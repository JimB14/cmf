<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Post;
use \App\Models\Category;
use \App\Mail;

/**
 * Authors controller
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
        //if SESSION is not set, send to login page
        if(!isset($_SESSION['user']))
        {
            header("Location: /login");
            exit();
        }
    }


    /**
     * Show the Admin Panel index page
     *
     * @return void
     */
    public function indexAction()
    {
        // retrieve GET variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // if user not logged in send to login page
        if(!$user_id)
        {
            header("Location: /login");
            exit();
        }
        else
        {
            // get user data
            $author = User::getUser($user_id);

            // test
            // echo '<pre>';
            // print_r($author);
            // echo '</pre>';
            // exit();

            // get author posts
            $posts = Post::getAuthorPosts($user_id, $limit=null);

            // test
            // echo '<pre>';
            // print_r($posts);
            // echo '</pre>';
            // exit();

            // get most recent post
            $last_post = Post::getLatestAuthorPost($user_id);

            // test
            // echo '<pre>';
            // print_r($last_post);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'author'    => $author,
                'posts'     => $posts,
                'last_post' => $last_post,
                'home'      => 'active'
            ]);
        }
    }


    /**
     * create new post
     *
     * @return view
     */
    public function createPostAction()
    {
        // retrieve user ID
        $user_id = $this->route_params['id'];

        // get author data
        $author = User::getAuthor($user_id);

        // test
        // echo '<pre>';
        // print_r($author);
        // echo '</pre>';

        // get post categories
        $categories = Category::getCategories();

        // render view
        View::renderTemplate('Admin/Add/post.html',[
            'author'        => $author,
            'categories'    => $categories
        ]);
    }


    /**
     * inserts new post into posts table
     *
     * @return view
     */
    public function submitPostAction()
    {
        // retrieve query string variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // insert new post
        $results = Post::insertPost($user_id);

        // store array items in variables
        $result     = $results['result'];
        $post_id    = $results['post_id'];
        $image      = $results['image'];
        $post_token = $results['post_token'];

        if($result)
        {
            // get new post
            $new_post = Post::getPostDraft($post_id);

            // test
            // echo '<pre>';
            // print_r($new_post);
            // echo '</pre>';
            // exit();

            // send notification
            $mail_result = Mail::newPostNotification($new_post, $image, $post_token);
        }

        if($mail_result)
        {
            $message = "New post successfully submitted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to Views/Admin/index.html
            echo '<script>';
            echo 'window.location.href="/admin/authors/index?user_id='.$user_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * displays posts for specific author
     *
     * @return Object  The broker's agents
     */
    public function showPosts()
    {
        $user_id = $this->route_params['id'];

        // echo $user_id; exit();

        // get posts
        $posts = Post::getAllAuthorPosts($user_id);

        // test
        // echo "<pre>";
        // print_r($posts);
        // echo "</pre>";
        // exit();

        // render view & pass $agents object
        View::renderTemplate('Admin/Show/posts.html', [
            'posts' => $posts
        ]);
    }



    public function changePostStatus()
    {
        // retrieve query string variables
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $post_id = (isset($_REQUEST['post_id'])) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $post_status = (isset($_REQUEST['post_status'])) ? filter_var($_REQUEST['post_status'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $post_status;
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // change status of post
        $result = Post::togglePostStatus($post_id, $post_status);

        if($result)
        {
          $message = "Post status successfully changed!";

          // display success message
          echo '<script>';
          echo 'alert("'.$message.'")';
          echo '</script>';

          // redirect user to Views/Admin/index.html
          echo '<script>';
          echo 'window.location.href="/admin/authors/show-posts/'.$user_id.'"';
          echo '</script>';
          exit();
        }
    }



    public function editPost()
    {
        // retrieve query string variable
        $post_id = (isset($_REQUEST['post_id'])) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        // $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // get post
        $post = Post::getPost($post_id);

        // get categories
        $categories = Category::getCategories();


        View::renderTemplate('Admin/Update/post.html', [
            'post'        => $post,
            'categories'  => $categories
        ]);
    }


    /**
     * updates referenced post
     *
     * @return Boolean
     */
    public function updatePost()
    {
        // retrieve GET variables
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $post_id = (isset($_REQUEST['post_id'])) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';

        // test
        // echo $user_id . '<br>';
        // echo $post_id;
        // exit();

        // test
        // if(!file_exists($_FILES['new_post_img']['tmp_name']) || !is_uploaded_file($_FILES['new_post_img']['tmp_name']))
        // {
        //   echo 'No upload';
        // }
        // else
        // {
        //     echo "File uploaded.";
        // }
        // exit();

        // update post
        $result = Post::updatePost($post_id, $user_id);

        // exit();

        if($result)
        {
            // store success message in variable
            $message = "Post successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/authors/show-posts/'.$user_id.'"';
            echo '</script>';
            exit();
        }
    }


    public function deletePost()
    {
        // retrieve query string variable
        $post_id = (isset($_REQUEST['post_id'])) ? filter_var($_REQUEST['post_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // delete post
        $result = Post::deletePost($post_id);

        if($result)
        {
            $message = "Post successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/authors/show-posts/'.$user_id.'"';
            echo '</script>';
            exit();
        }
    }




























    /**
     * retrieve data for specified broker & render view
     *
     * @return view
     */
    public function showListings()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $listings = Listing::getBusinessListingsForAdmin($broker_id, $limit = null);

        // test
        // echo $_SESSION['broker_id'];
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby='broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'        => $listings,
            'agents'          => $agents,
            'broker_type'     => $broker_type,
            'managelistings'  => 'active'
        ]);
    }



    /**
     * retrieves listing record by broker ID
     *
     * @return view
     */
    public function showListingsById()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $listings = Listing::getListingsById($broker_id);

        // test
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings' => $listings,
            'agents'   => $agents
        ]);
    }




    public function showListingsByAgentLastName()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $listings = Listing::getListingsByAgentLastName($broker_id);

        // test
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings' => $listings,
            'agents'   => $agents
        ]);

    }



    /**
     * retrieves listings by agent last name or client ID
     *
     * @return view
     */
    public function searchListingsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id   = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox    = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // test
        // echo "Form & URL values:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'input value: ' . $input_value . '<br>';
        // echo 'checkbox: ' . $checkbox . '<br><br>';
        // exit();

        // if checkbox is checked, user searching by listing ID
        if($checkbox)
        {
            // assign input value to $client_id & make $last_name = null
            $clients_id = $input_value;
            $last_name = null;
            // echo "If checkbox is checked<br>";
            // echo 'client_id / field input: ' . $clients_id . '<br>';
            // echo 'last_name / should be null: ' . $last_name . '<br><br>';
            // exit();
        }
        if($checkbox == null)
        {
            $last_name = $input_value;
            $clients_id = null;
            // echo "If checkbox not checked<br>";
            // echo 'last_name: ' . $last_name . '<br>';
            // echo 'client_id / should be null: ' . $clients_id . '<br><br>';
            // exit();
        }

        // test
        // echo "After conditional statement:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'last_name / value if being searched: ' . $last_name . '<br>';
        // echo 'clients_id / value if being searched: ' . $clients_id . '<br>';
        // exit();

        // get listings and pagetitle
        $results = Listing::getListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }



    /**
     * retrieves real estate listings by listing agent last name or client ID
     *
     * @return Object   The listings
     */
    public function searchRealtyListingsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // test
        // echo "Form & URL values:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'input value: ' . $input_value . '<br>';
        // echo 'checkbox: ' . $checkbox . '<br><br>';
        // exit();

        // if checkbox is checked, user searching by listing ID
        if($checkbox)
        {
            // assign input value to $client_id & make $last_name = null
            $clients_id = $input_value;
            $last_name = null;
            // echo "If checkbox is checked<br>";
            // echo 'client_id / field input: ' . $clients_id . '<br>';
            // echo 'last_name / should be null: ' . $last_name . '<br><br>';
            // exit();
        }
        if($checkbox == null)
        {
            $last_name = $input_value;
            $clients_id = null;
            // echo "If checkbox not checked<br>";
            // echo 'last_name: ' . $last_name . '<br>';
            // echo 'client_id / should be null: ' . $clients_id . '<br><br>';
            // exit();
        }

        // test
        // echo "After conditional statement:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'last_name / value if being searched: ' . $last_name . '<br>';
        // echo 'clients_id / value if being searched: ' . $clients_id . '<br>';
        // exit();

        // get listings and pagetitle
        $results = Realtylisting::getRealtyListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/realty-listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }



    public function searchLeadsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id   = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox    = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // test
        // echo "Form & URL values:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'input value: ' . $input_value . '<br>';
        // echo 'checkbox: ' . $checkbox . '<br><br>';
        // exit();

        // if checkbox is checked, user searching by listing ID
        if($checkbox)
        {
            // assign input value to $client_id & make $last_name = null
            $clients_id = $input_value;
            $last_name = null;
            // echo "If checkbox is checked<br>";
            // echo 'client_id / field input: ' . $clients_id . '<br>';
            // echo 'last_name / should be null: ' . $last_name . '<br><br>';
            // exit();
        }
        if($checkbox == null)
        {
            $last_name = $input_value;
            $clients_id = null;
            // echo "If checkbox not checked<br>";
            // echo 'last_name: ' . $last_name . '<br>';
            // echo 'client_id / should be null: ' . $clients_id . '<br><br>';
            // exit();
        }

        // test
        // echo "After conditional statement:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'last_name / value if being searched: ' . $last_name . '<br>';
        // echo 'clients_id / value if being searched: ' . $clients_id . '<br>';
        // exit();

        // get listings and pagetitle
        $results = Lead::getLeadsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        //$agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/leads.html', [
            'leads'       => $results['leads'],
            'pagetitle'   => $results['pagetitle'],
            // 'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }




    public function searchListingsByClientId()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // retrieve form data
        $clients_id = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // display alert & redirect to same page on empty form submission
        if($clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id=' .$_SESSION['user_id'].'"';
            echo '</script>';
            exit();
        }

        // get listings and pagetitle
        $results = Listing::getListingsBySearchCriteria($broker_id, $last_name=null, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }




    public function searchRealtyListingsByClientId()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // retrieve form data
        $clients_id = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';


        // display alert & redirect to same page on empty form submission
        if($clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id=' .$_SESSION['user_id'].'"';
            echo '</script>';
            exit();
        }


        // get listings and pagetitle
        $results = Realtylisting::getRealtyListingsBySearchCriteria($broker_id, $last_name=null, $clients_id, $limit=null);


        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/realty-listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }





    public function editAgent()
    {
      // retrieve GET variable
      $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
      $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

      // get agent data from BrokerAgent model
      $agent = BrokerAgent::getAgent($agent_id);

      // get states for drop-down
      $states = State::getStates();

      // get broker company name
      $broker_company_name = Broker::getBrokerCompanyName($broker_id);

      // test
      // echo "<pre>";
      // print_r($agent);
      // echo "</pre>";
      // exit();

      // get company type (broker type = business(1), realty(2), both(3))
      $broker_type = Broker::getBrokerCompanyType($broker_id);

      View::renderTemplate('Admin/Update/edit-agent.html', [
          'agent'         => $agent,
          'states'        => $states,
          'broker_type'   => $broker_type,
          'company_name'  => $broker_company_name
      ]);
    }




    public function updateAgent()
    {
        // retrieve GET variables
        $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update agent record
        $result = BrokerAgent::updateAgent($agent_id, $broker_id);

        if($result)
        {
            $message = "Agent data successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function deleteAgent()
    {
        // retrieve GET variables
        $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';


        // check for business listings for this agent
        $listings = Listing::getAllAgentListings($agent_id, $limit = null);

        // test
        // echo "<pre>";
        // echo print_r($listings);
        // echo "</pre>";
        // exit();

        if(!empty($listings))
        {
            $errorMessage = "Error. An agent with listings cannot be deleted.
                Please re-assign listings to another agent before attempting to delete.";

            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // check for real estate listings for this agent
        $realty_listings = Realtylisting::getListingsByAgent($broker_id, $agent_id);

        // test
        // echo "<pre>";
        // echo print_r($realty_listings);
        // echo "</pre>";
        // exit();

        if(!empty($realty_listings))
        {
            $errorMessage = "Error. An agent with listings cannot be deleted.
                Please re-assign listings to another agent before attempting to delete.";

            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // get profile photo file name
        $profile_photo = BrokerAgent::getProfilePhotoName($agent_id);

        // if profile photo exists delete from server
        if($profile_photo)
        {
            // remove profile photo from server
            $result = BrokerAgent::deleteProfilePhoto($profile_photo);
        }

        // delete agent from broker_agents table
        $result = BrokerAgent::deleteAgent($agent_id);

        // if agent successfully deleted
        if($result)
        {
            $message = "Agent successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';
        }
    }




    public function searchAgents()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // retrieve form data
        $last_name = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';

        // get agent data
        $results = BrokerAgent::getAgents($broker_id, $last_name);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view & pass $agents object
        View::renderTemplate('Admin/Show/agents.html', [
            'agents'      => $results['agents'],
            'pagetitle'   => $results['pagetitle'],
            'broker_id'   => $broker_id,
            'broker_type' => $broker_type
        ]);
    }




    public function updateCompanyProfile()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update company data in brokers table
        $result = Broker::updateCompanyProfile($broker_id);

        if($result)
        {
            $message = "Company profile successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Company data not updated.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function updateCompanyBrokerPhoto()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // upload new image
        $result = Broker::updateCompanyBrokerPhoto($broker_id);

        if($result)
        {
            $message = "Broker photo successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. No file chosen.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function updateCompanyLogo()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update company logo
        $result = Broker::updateCompanyLogo($broker_id);

        if($result)
        {
            $message = "Company logo successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. No file chosen.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }



    /**
     * retrieves listing record by listing ID & Broker ID
     *
     * @return view
     */
    public function editListing()
    {
        // retrieve GET variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // get listing data
        $listing = Listing::getListingDetailsForAdmin($listing_id);

        // test
        // echo "<pre>";
        // echo print_r($listing);
        // echo "</pre>";
        // exit();

        // get all agents (for drop-down)
        $agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo "<pre>";
        // echo print_r($agents);
        // echo "</pre>";
        // exit();

        // get business categories (for drop-down)
        $categories = Category::getCategories();

        // get broker company name
        $broker_company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get states for drop-down
        $states = State::getStates();

        // test
        // echo "<pre>";
        // print_r($agent);
        // echo "</pre>";
        // exit();

        View::renderTemplate('Admin/Update/edit-listing.html', [
            'listing'             => $listing,
            'agents'              => $agents,
            'categories'          => $categories,
            'broker_company_name' => $broker_company_name,
            'states'              => $states,
            'broker_type'         => $broker_type
        ]);
    }



    /**
     * update listing record in database
     *
     * @return boolean
     */
    public function updateListing()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update listing
        $result = Listing::updateListing($listing_id, $broker_id);

        if($result)
        {
            $message = "Listing successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Unable to update listing.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }



    /**
     * updates images for specified listing
     *
     * @return boolean
     */
    public function editListingImages()
    {
        // retrieve variables - listing ID & broker ID
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update images
        $result = Listing::updateListingImages($id, $broker_id);

        if($result)
        {
            $message = "Listing images successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user back to this edit page
            echo '<script>';
            // echo 'window.location.href="/admin/brokers/edit-listing?id='.$id.'&broker_id='.$broker_id.'"';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Unable to update listing images.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }



    /**
     * deletes listing by ID
     *
     * @return boolean
     */
    public function deleteListing()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete listing from listing (cascades to listing_financial & listing_images)
        $result = Listing::deleteListing($listing_id);

        if($result)
        {
            $message = "Business listing successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Listing not deleted.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function changePassword()
    {
        // retrieve query string variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // update password
        $result = User::updatePassword($user_id);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Your password was successfully changed! You must log back in.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/logout"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id='.$user_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function changeLoginEmail()
    {
        // retrieve variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // update password
        $result = User::updateLoginEmail($user_id);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Your login email was successfully changed! You must log back in.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/logout"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id='.$user_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function deleteListingImage()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';
        $image = (isset($_REQUEST['image'])) ? filter_var($_REQUEST['image'], FILTER_SANITIZE_STRING) : '';

        // delete from listing_images table
        $result = Listing::deleteListingImage($listing_id, $image);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Image successfully deleted.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            //echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo 'window.location.href="/admin/brokers/edit-listing?id='.$listing_id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user back to this page to see failed delete
            echo '<script>';
            echo 'window.location.href="/admin/brokers/edit-listing?id='.$listing_id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }

}
