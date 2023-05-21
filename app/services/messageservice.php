<?php
namespace Services;

use Repositories\MessageRepository;

class MessageService {

    private $repository;

    function __construct()
    {
        $this->repository = new MessageRepository();
    }

    public function getfriendsSentOrReceiveMessage($userId) {
        return $this->repository->getfriendsSentOrReceiveMessage($userId);
    }

    public function getOneConversation($loggedInUser,$friendId) {
        return $this->repository->getOneConversation($loggedInUser,$friendId);
    }

    public function insert($message) {       
        return $this->repository->insert($message);        
    }


    public function delete($id) {       
        return $this->repository->delete($id);        
    }
}

?>