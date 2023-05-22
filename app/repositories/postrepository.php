<?php

namespace Repositories;

//use Models\Category;
use Models\Post;
use PDO;
use PDOException;
use Repositories\Repository;

class PostRepository extends Repository
{
    function getAll($offset, $limit)
    {
        try {
            $query = "SELECT * FROM post";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $posts = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {               
                $posts[] = $this->rowToProduct($row);
            }

            return $posts;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT * FROM post  WHERE postId = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            if ($row == null) {
                return null;
            }
            $post = $this->rowToProduct($row);

            return $post;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToProduct($row) {
        $post = new post();
        $post->postId = $row['postId'];
        $post->userId = $row['userId'];
        $post->date = $row['date'];
        $post->postIdea = $row['postIdea'];
        $post->privacy = $row['privacy'];
        $post->status = $row['status'];
        return $post;
    }

    function insert($post)
    {
        try {
            $stmt = $this->connection->prepare("INSERT into post (userId, date, postIdea, privacy,status) VALUES (?,?,?,?,?)");

            $stmt->execute([$post->userId, $post->date, $post->postIdea, $post->privacy,$post->status]);

            $post = $this->connection->lastInsertId();

            return $this->getOne($post);
        } catch (PDOException $e) {
            echo $e;
        }
     }


    function update($post, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE post SET postIdea = ?, privacy = ? WHERE postId = ?");

            $stmt->execute([$post->postIdea, $post->privacy, $id]);

            return $this->getOne($id);
        } catch (PDOException $e) {
            echo $e;
        }
     }

     function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM post WHERE postId = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
     }
     public function getPostByUserId($userId,$offset, $limit)
     {
        try {
            $query = "SELECT * FROM post Where userId = :userId";
            
            // if (isset($limit) && isset($offset)) {
            //     $query .= " LIMIT :limit OFFSET :offset ";
            // }
            $stmt = $this->connection->prepare( "SELECT * FROM post Where userId = :userId");
            // if (isset($limit) && isset($offset)) {
            //     $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            //     $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            // }
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            $posts = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {               
                $posts[] = $this->rowToProduct($row);
            }

            return $posts;
        } catch (PDOException $e) {
            echo $e;
        }
     }

     // this can be done only by admin
     function blockPost($id,$status)
     {
        try {
            $stmt = $this->connection->prepare("UPDATE post SET status = ? WHERE postId = ?");

            $stmt->execute([$status, $id]);

            return $this->getOne($id);
        } catch (PDOException $e) {
            echo $e;
        }
     }
}
