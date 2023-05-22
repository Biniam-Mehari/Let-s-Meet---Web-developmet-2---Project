<?php

namespace Controllers;

use Exception;
use Services\PostService;

class PostController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new PostService();
        
        
    }

    public function getAll()
    {

        if (!$this->checkForJwt()) {
            return ;
         }

        $offset = 0;
        $limit = 10;
        $userId = null;

        //TODO: check for limit and offset
        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        
        // gets all post of one user if user id is given, 
        if (isset($_GET["userId"])) {
            $userId = $_GET["userId"];
            $posts = $this->service->getPostByUserId($userId,$offset, $limit);
            if (!$posts) {
                $this->respondWithError(404, "post not found");
                return;
            }
            
            //if not it will show all post which is public
        } else{
            $posts = $this->service->getAll($offset, $limit);
            if (!$posts) {
                $this->respondWithError(404, "post not found");
                return;
            }
        }  
      
       
        $this->respond($posts);
    }

    public function getOne($id)
    {
        //check if user logged in and provide token
        $tocken = $this->checkForJwt();
        if (!$tocken) {
            return ;
         }

        // extract one post from db
        $post = $this->service->getOne($id);

        //  error checking that returns a 404 if the post is not found in the DB
        if (!$post) {
            $this->respondWithError(404, "post not found");
            return;
        }

        //check if post is private and prevent from displaying by other users
        if ( $tocken->data->id != $post->userId and  $post->privacy=="private") {
            $this->respondWithError(400, "you cant see this post it is private");
            return;
         }

        $this->respond($post);
    }

    public function create()
    {
        try {

            $post = $this->createObjectFromPostedJson("Models\\Post");

            //check if a user is posting for himself
            $tocken = $this->checkForJwt();
          if ( $tocken->data->id != $post->userId ) {
            $this->respondWithError(400, "you cant post for other user");
            return;
          }
             // check if all information of a user is filled
            if (!isset( $post->userId) || !isset( $post->postIdea) 
            || !isset( $post->privacy) ) {
                $this->respondWithError(400, "userId, postIdea and privacy must be filled ");
                return;
            }
            //set date and status of post
            $post->date = date('Y-m-d H:i:s');

            //staus can be changed later only by admin. 
            //example if a post is sensitive admin can change the status to blokked and no one will see it
            $post->status = "clear";
            $post = $this->service->insert($post);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($post);
    }

    public function update($id)
    {
        try {
            $post = $this->createObjectFromPostedJson("Models\\Post");

            //check if a user is updating his post 
            $tocken = $this->checkForJwt();
          if ( $tocken->data->id != $post->userId ) {
            $this->respondWithError(400, "you cant update this post is from other user");
            return;
          }

             // check if all information of a user is filled
             if (!isset( $post->postIdea) 
             || !isset( $post->privacy) ) {
                 $this->respondWithError(400, "postIdea and privacy must be filled ");
                 return;
             }
            $post = $this->service->update($post, $id);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($post);
    }

    public function delete($id)
    {
        try {
                //check if a user is updating his post 
                $tocken = $this->checkForJwt();
                if ( $tocken->data->id != $id ) {
                  $this->respondWithError(400, "you cant delete this post is for other user");
                  return;
                }
            $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }
    
    function blockPost($id)
    {
        try {

                //check if a user is updating his post 
                $tocken = $this->checkForJwt();
                if ( $tocken->data->role != "admin" ) {
                  $this->respondWithError(400, "you cant block this post. it ia allowed only for admin");
                  return;
                }
            $post = $this->createObjectFromPostedJson("Models\\Post");
               //check if status is allowed or blocked
        if ($post->status != "allowed" and $post->status != "blocked") {
            
            $this->respondWithError(400, "status must be filled as allowed or blocked ");
        return;
        }
            $post = $this->service->blockPost($id,$post->status);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($post);
    }
}
