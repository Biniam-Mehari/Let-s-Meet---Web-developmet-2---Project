<?php
namespace Services;

use Repositories\PostRepository;

class PostService {

    private $repository;

    function __construct()
    {
        $this->repository = new PostRepository();
    }

    public function getAll($offset, $limit) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function insert($post) {       
        return $this->repository->insert($post);        
    }

    public function update($post, $id) {       
        return $this->repository->update($post, $id);        
    }

    public function delete($item) {       
        return $this->repository->delete($item);        
    }

    public function getPostByUserId($userId,$offset, $limit)
    {
        return $this->repository->getPostByUserId($userId,$offset, $limit);
    }

    // this is only for admin
    function blockPost($id,$status)
    {
        return $this->repository->blockPost($id,$status); 
    }
}

?>