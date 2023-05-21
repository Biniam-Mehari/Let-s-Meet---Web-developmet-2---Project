<?php
namespace Services;

use Repositories\UserRepository;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function checkEmailPassword($username, $password) {
        return $this->repository->checkEmailPassword($username, $password);
    }
    public function registerUser($postedUser){
        return $this->repository->registerUser($postedUser);
    }
    public function update($user, $id) {       
        return $this->repository->update($user, $id);        
    }

    public function getSecretCodeByEmail($email)
    {
        return $this->repository->getSecretCodeByEmail($email);
    }
    public function changePassword($id,$password)
    {
        return $this->repository->changePassword($id,$password);
    }
}

?>