<?php

namespace Controllers;

use Exception;
use Services\FriendsService;

class FriendsController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new FriendsService();
    }

    public function getAllMyFriends($userId)
    {
        $offset = NULL;
        $limit = NULL;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $friends = $this->service->getAllMyFriends($userId,$offset, $limit);
        if (!$friends) {
            $this->respondWithError(404, "your friends list is empty");
            return;
        }
        $this->respond($friends);
    }

    public function create()
    {
        try {
           
            $friend = $this->createObjectFromPostedJson("Models\\Friends");

           $newFriends= $this->service->insert($friend);
           if (!$newFriends) {
            $this->respondWithError(404, "user does not exist");
            return;
        }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($newFriends);
    }

    public function update($friendsId)
    {
        try {
            $friends = $this->createObjectFromPostedJson("Models\\Friends");
           $updatedStatus= $this->service->update($friends, $friendsId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($updatedStatus);
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
}
