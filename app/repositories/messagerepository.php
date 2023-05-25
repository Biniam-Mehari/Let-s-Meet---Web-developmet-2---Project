<?php

namespace Repositories;


use Models\Message;
use Models\User;


use PDO;
use PDOException;
use Repositories\Repository;


class MessageRepository extends Repository
{
    public function getfriendsSentOrReceiveMessage($userId)
    {
       try {
          
           $stmt = $this->connection->prepare( "SELECT * FROM message Where fromUser = :fromUser or toUser=:toUser");
          
           $stmt->bindParam(':fromUser', $userId);
           $stmt->bindParam(':toUser', $userId);
           $stmt->execute();

           $friendsWithNessage = array();
           while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {     
              $user = $this->rowToProductUser($row['fromUser']);
              if ($user->id != $userId) {
               if (!in_array( $user, $friendsWithNessage)) {
                $friendsWithNessage[] = $user;
               }
            }

               $user = $this->rowToProductUser($row['toUser']);
               if ($user->id != $userId) {
               if (!in_array( $user, $friendsWithNessage)) {
                $friendsWithNessage[] = $user;
               }
            }
           }

           return $friendsWithNessage;
       } catch (PDOException $e) {
           echo $e;
       }
    }

    function rowToProductUser($id)
    {
        $user = new User();
        $user->id= $id;
        $getEmail = $this->connection->prepare( "SELECT email FROM user Where id=:id");
        $getEmail->bindParam(':id', $user->id);
        $getEmail->execute();
        $getEmail->setFetchMode(PDO::FETCH_ASSOC);
        $row = $getEmail->fetch();
        $user->email = $row['email'];
        return $user;
    }
   

    function getOneConversation($loggedInUser,$friendId)
    {
        try {
         
            $stmt = $this->connection->prepare("SELECT * FROM message  WHERE (fromUser = :loggedInUser and toUser= :friendId) or (fromUser = :friendId and toUser= :loggedInUser)");
            $stmt->bindParam(':loggedInUser', $loggedInUser);
            $stmt->bindParam(':friendId', $friendId);
            $stmt->bindParam(':friendId', $friendId);
            $stmt->bindParam(':loggedInUser', $loggedInUser);
            $stmt->execute();

            $messages = array();
           
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {     
                     
                $messages[] = $this->rowToProduct($row);
                
            }
            return $messages;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function rowToProduct($row) {
        $message = new Message();
        $message->messageId = $row['messageId'];
        $message->fromUserId = $row['fromUser'];
        $message->toUserId = $row['toUser'];
        $message->date = $row['date'];
        $message->message = $row['message'];
        return $message;
    }

    function insert($message)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $stmt = $this->connection->prepare("INSERT INTO message ( fromUser, toUser, date, message) VALUES (?,?,?,?)");

            $stmt->execute([$message->fromUserId, $message->toUserId, $date, $message->message]);

           // $messageSent = $this->connection->lastInsertId();

            return $this->getOneConversation($message->fromUserId,$message->toUserId);
        } catch (PDOException $e) {
            echo $e;
        }
     }


     function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM message WHERE messageId = :messageId");
            $stmt->bindParam(':messageId', $id);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
     }

     function checkIfOneConversationExist($loggedInUser,$friendId)
     {
        $stmt = $this->connection->prepare( "SELECT * FROM message Where fromUser = :fromUser or toUser=:toUser");
          
           $stmt->bindParam(':fromUser', $userId);
           $stmt->bindParam(':toUser', $userId);
           $stmt->execute();
     }
   
}
