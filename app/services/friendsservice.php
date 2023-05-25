<?php
namespace Services;

use Repositories\FriendsRepository;

class FriendsService {

    private $repository;

    function __construct()
    {
        $this->repository = new FriendsRepository();
    }

    public function getAllMyFriends($userId) {
        return $this->repository->getAllMyFriends($userId);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function insert($friend) {       
        return $this->repository->insert($friend);        
    }

    public function update($friends, $friendsId) {       
        return $this->repository->update($friends, $friendsId);        
    }

    public function delete($item) {       
        return $this->repository->delete($item);        
    }
}

?>