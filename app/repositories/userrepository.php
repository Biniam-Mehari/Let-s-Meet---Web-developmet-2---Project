<?php

namespace Repositories;

use Models\User;
use PDO;
use PDOException;
use Repositories\Repository;

class UserRepository extends Repository
{
    function checkEmailPassword($email, $password)
    {
        try {
            // retrieve the user with the given username
            $stmt = $this->connection->prepare("SELECT id, password, email,role,firstName,lastName FROM user WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            $user = $stmt->fetch();
            
            if ($user==null) {
                return false;
            }
            // verify if the password matches the hash in the database
            $result = $this->verifyPassword($password, $user->password);

            if (!$result)
                return false;

            // do not pass the password hash to the caller
            $user->password = "";

            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function registerUser($postedUser)
    {
        $role = "user";
         // hash the password
       $hashedpassword = $this->hashPassword($postedUser->password);
       // retrieve the user with the given username
       $stmt = $this->connection->prepare("INSERT INTO user ( firstName , lastName, password, email , role , secretCode) VALUES (:firstName , :lastName, :password, :email , :role,:secretCode)");
       $stmt->bindParam(':firstName', $postedUser->firstName);
       $stmt->bindParam(':lastName', $postedUser->lastName);
       $stmt->bindParam(':password', $hashedpassword);
       $stmt->bindParam(':email', $postedUser->email);
       $stmt->bindParam(':role',$role);
       $stmt->bindParam(':secretCode',$secretCode);
       $stmt->execute();
    }

    // hash the password (currently uses bcrypt)
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // verify the password hash
    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }

    // update information of the user
    function update($user, $id)
    {
        try {
            if ($user->firstName == "" || $user->lastName==""||$user->secretCode=="") {
                return "fill all the needed information";
            }
            $stmt = $this->connection->prepare("UPDATE user SET firstName = ?, lastname = ?, secretCode = ? WHERE id = ?");
            $stmt->execute([$user->firstName, $user->lastName, $user->secretCode, $id]);

            return $this->getOneAccountById($id);
        } catch (PDOException $e) {
            echo $e;
        }
     }

     //checks the given secret code with the already in database
    function getSecretCodeByEmail($email)
     {
        try {
           
            $stmt = $this->connection->prepare("SELECT * FROM user  WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            if ($row == null) {
                return null;
            }
            $user= new User();
            $user->secretCode = $row["secretCode"];
            $user->id = $row["id"];
            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
     }

     // after checking the secret code a way of changing a new password
     function changePassword($id,$password)
     {
        try {
            $hashedpassword = $this->hashPassword($password);
            $stmt = $this->connection->prepare("UPDATE user SET password = ? WHERE id = ?");

            $stmt->execute([$hashedpassword, $id]);

            return "Your password has been updated";
        } catch (PDOException $e) {
            echo $e;
        }
     }

     // get one account by id
     function getOneAccountById($id)
     {
        try {
           
            $stmt = $this->connection->prepare("SELECT * FROM user  WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            if ($row == null) {
                return null;
            }
           
            return $this->rowToUser($row);
        } catch (PDOException $e) {
            echo $e;
        }
     }

    // changes the row data to type of user
     function rowToUser($row)
     {
        $user= new User();
        $user->secretCode = $row["secretCode"];
        $user->id = $row["id"];
        $user->firstName = $row["firstName"];
        $user->lastName = $row["lastName"];
        return $user;
     }
}
