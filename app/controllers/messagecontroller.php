<?php

namespace Controllers;

use Exception;
use Services\MessageService;

class MessageController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new MessageService();
    }

    public function getfriendsSentOrReceiveMessage($userId)
    {
        $tocken = $this->checkForJwt();
        if (!$tocken) {
            return;
        } 
         if ($tocken->data->id != $userId ) {
           $this->respondWithError(400, "you can't see other user messages");
           return;
         }
        $friendshaveMessage = $this->service->getfriendsSentOrReceiveMessage($userId);
        if (!$friendshaveMessage) {
            $this->respondWithError(404, "your message list is empty");
            return;
        }
        $this->respond($friendshaveMessage);
    }

    public function getOneConversation()
    {

      

        $tocken = $this->checkForJwt();
        if (!$tocken) {
            return;
        } 
        if (!isset($_GET['id'])){
            $this->respondWithError(400, "provide an Id of user to see your conversation");
            return;
        }
        $loggedInId = $tocken->data->id;
        $friendId = $_GET['id'];
        $conversation = $this->service->getOneConversation( $loggedInId,$friendId);

        // error checking that returns a 404 if the conversation is not found in the DB
        if (!$conversation) {
            $this->respondWithError(404, "conversation not found");
            return;
        }

       $this->respond($conversation);
    }

    public function create()
    {
        try {
           
            $tocken = $this->checkForJwt();
        if (!$tocken) {
            return;
        } 
      
            $message = $this->createObjectFromPostedJson("Models\\Message");
            $message->fromUserId = $tocken->data->id;

            // check if all information of a user is filled
         if (!isset( $message->toUserId) 
         || !isset( $message->message) ) {
             $this->respondWithError(400, "toUserId and message must be filled ");
             return;
         }

            if ($message->fromUserId == $message->toUserId) {
                $this->respondWithError(404, "you cant send message to yourself");
            return;
            }
           $messageSent= $this->service->insert($message);
           // return  $messageSent;
        
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($messageSent);
    }


    public function delete($id)
    {
        try {
            $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond("Message deleted ");
    }
}
