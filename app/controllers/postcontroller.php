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
        $offset = NULL;
        $limit = NULL;
        $userId = null;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }
        if (isset($_GET["userId"])) {
            $userId = $_GET["userId"];
            $posts = $this->service->getPostByUserId($userId,$offset, $limit);
            if (!$posts) {
                $this->respondWithError(404, "post not found");
                return;
            }
            
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
        $post = $this->service->getOne($id);

        // we might need some kind of error checking that returns a 404 if the product is not found in the DB
        if (!$post) {
            $this->respondWithError(404, "post not found");
            return;
        }

        $this->respond($post);
    }

    public function create()
    {
        try {
            $post = $this->createObjectFromPostedJson("Models\\Post");
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
            $post = $this->service->update($post, $id);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($post);
    }

    public function delete($id)
    {
        try {
            $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }
    
    function blockPost($id)
    {
        try {
            $post = $this->createObjectFromPostedJson("Models\\Post");
            $post = $this->service->blockPost($id,$post->status);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($post);
    }
}
