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
        $friendshaveMessage = $this->service->getfriendsSentOrReceiveMessage($userId);
        if (!$friendshaveMessage) {
            $this->respondWithError(404, "your message list is empty");
            return;
        }
        $this->respond($friendshaveMessage);
    }

    public function getOneConversation()
    {

       // todo use a direct input
        $loggedInUser = 1;
        $friendId = 2;
        $conversation = $this->service->getOneConversation($loggedInUser,$friendId);

        // we might need some kind of error checking that returns a 404 if the product is not found in the DB
        if (!$conversation) {
            $this->respondWithError(404, "post not found");
            return;
        }

       $this->respond($conversation);
    }

    public function create()
    {
        try {
           
            $message = $this->createObjectFromPostedJson("Models\\Message");

            if ($message->fromUserId == $message->toUserId) {
                $this->respondWithError(404, "you cant send message to yourself");
            return;
            }
           $messageSent= $this->service->insert($message);
            return  $messageSent;
        
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
