<?php

namespace Controllers;

use Exception;
use Services\FriendsService;
use Services\UserService;

class FriendsController extends Controller
{
    private $service;
    private $userService;

    // initialize services
    function __construct()
    {
        $this->service = new FriendsService();
        $this->userService = new UserService();
    }

    public function getAllMyFriends($userId)
    {
         //check if a user is logged in
         $tocken = $this->checkForJwt();
        if (!$tocken) {
            return;
        } 
         if ($tocken->data->id != $userId and $tocken->data->role != "admin") {
           $this->respondWithError(400, "you can't see other user friends");
           return;
         }

         // this is only for admin to receive a message if a user does not exist
         $user = $this->userService->checkUserExist($userId);
         if ($user==false) {
            $this->respondWithError(404, "User does not exist");
            return;
         }

        $friends = $this->service->getAllMyFriends($userId);
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

            !$tocken = $this->checkForJwt();
            if (!$tocken) {
                return;
            } 
             if ($tocken->data->id != $friend->user1 and $tocken->data->role != "admin") {
               $this->respondWithError(400, "you can't see other user friends");
               return;
             }
             if ($tocken->data->id != $friend->user1) {
                $this->respondWithError(400, "you can't send friend request for other user");
                return;
              }

             // check if user exist
            //check if users exist before adding to the datatbase
            $user1 = $this->userService->checkUserExist($friend->user1);
            $user2 = $this->userService->checkUserExist($friend->user2);
            if ($user1==false || $user2==false) {
                return "user does not exist";
            }

           $newFriends= $this->service->insert($friend);
        
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($newFriends);
    }

    // this method will accept a friend request will be used by user 2
    public function update($friendsId)
    {
        try {
            $tocken = $this->checkForJwt();
            if (!$tocken) {
                return;
            } 
            //retracting if the user is the same as the database registerd user
            $checkUser= $this->service->getOne($friendsId);
            if ($tocken->data->id != $checkUser->user2) {
                $this->respondWithError(400, "you can't accept friend request for other user");
                return;
              }
            $friends = $this->createObjectFromPostedJson("Models\\Friends");
           $updatedStatus= $this->service->update($friends, $friendsId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($updatedStatus);
    }

    // can be used by user1 or user to to unfriend
    public function delete($friendsId)
    {
        try {
            $tocken = $this->checkForJwt();
            if (!$tocken) {
                return;
            } 
            //retracting if the user is the same as the database registerd user
            $checkUser= $this->service->getOne($friendsId);
            if ($tocken->data->id != $checkUser->user2 and $tocken->data->id != $checkUser->user1) {
                $this->respondWithError(400, "you can't unfriend for other user");
                return;
              }
            $this->service->delete($$friendsId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }
}
