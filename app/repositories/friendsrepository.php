<?php

namespace Repositories;

use Models\Friends;
use PDO;
use PDOException;
use Repositories\Repository;


class FriendsRepository extends Repository
{
    function getAllMyFriends($userId)
    {

        try {
            $query = "SELECT * FROM post Where userId = :userId";


            $stmt = $this->connection->prepare("SELECT * FROM friends Where user1 = :user1 or user2 = :user2");

            $stmt->bindParam(':user1', $userId);
            $stmt->bindParam(':user2', $userId);
            $stmt->execute();

            $myFriends = array();

            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $myFriends[] = $this->rowToProduct($row);
            }
            if ($myFriends == null) {
                return null;
            }
            return $myFriends;
        } catch (PDOException $e) {
            echo $e;
        }
    }

   


    function rowToProduct($row)
    {
        $friend = new Friends();
        $friend->friendsId = $row['friendsId'];
        $friend->user1 = $row['user1'];
        $friend->user2 = $row['user2'];
        $friend->dateRequest = $row['dateRequest'];
        if ($row['dateApproved'] != null) {
            $friend->dateApproved = $row['dateApproved'];
        } else {
            $friend->dateApproved = "requested";
        }
        $friend->status = $row['status'];
        return $friend;
    }

    //  ???
    function getOne($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM friends WHERE friendsId = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            if ($row == null) {
                return null;
            }
            $friend = $this->rowToProduct($row);

            return $friend;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function insert($friend)
    {
        try {

            //check if users are already friends or a request has been sent
            $checkIfAlreadyFriends = $this->checkIfUersAreFriends($friend->user1, $friend->user2);
            if ($checkIfAlreadyFriends == true) {
                return "users are already friends";
            }
            // $dateRequsted = "2017-06-15 09:34:21";
            // date_default_timezone_set('Netherlands/Rotterdam');
            $dateRequsted = date('Y-m-d H:i:s');
            $status = "requested";
            $stmt = $this->connection->prepare("INSERT INTO friends( user1, user2, dateRequest, status ) VALUES ( ?,?,?,?)");

            $stmt->execute([$friend->user1, $friend->user2, $dateRequsted, $status]);

            $friend  = $this->connection->lastInsertId();

            return $this->getOne($friend);
        } catch (PDOException $e) {
            echo $e;
        }
    }



    function checkIfUersAreFriends($userId1, $userId2)
    {
        $stmt = $this->connection->prepare("SELECT * FROM friends WHERE (user1=? And user2=?) or (user1=? And user2=?)");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);

        $friends = $stmt->fetch();

        if ($friends == null) {
            return false;
        }
        return true;
    }


    function update($friends, $friendsId)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE friends SET status = ? WHERE friendsId = ?");

            $stmt->execute([$friends->status, $friendsId]);

            $friends = $this->getOne($friendsId);
            return $friends;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM friends WHERE friendsId = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }
}
