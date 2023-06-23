<?php

namespace Controllers;
use Exception;
use Services\UserService;
use \Firebase\JWT\JWT;

class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new UserService();
    }

    public function login() {

        
       // read user data from request body
       $postedUser = $this->createObjectFromPostedJson("Models\\User");

       if (!isset( $postedUser->email) || !isset( $postedUser->password)) {
        $this->respondWithError(400, "email and password must be filled ");
        return;
    }

       // get user from db
       $user = $this->service->checkEmailPassword($postedUser->email, $postedUser->password);

       // if the method returned false, the username and/or password were incorrect
       if($user == null) {
           $this->respondWithError(401, "Invalid login");
           return;
       }

       // generate jwt
       $tokenResponse = $this->generateJwt($user);       

       $this->respond($tokenResponse);    
    }
    public function generateJwt($user) {
        
        $secret_key = "YOUR_SECRET_KEY";

        $issuer = "THE_ISSUER"; // this can be the domain/servername that issues the token
        $audience = "THE_AUDIENCE"; // this can be the domain/servername that checks the token

        $issuedAt = time(); // issued at
        $notbefore = $issuedAt; //not valid before 
        $expire = $issuedAt + 6000; // expiration time is set at +600 seconds (10 minutes)

        // JWT expiration times should be kept short (10-30 minutes)
        // A refresh token system should be implemented if we want clients to stay logged in for longer periods

        // note how these claims are 3 characters long to keep the JWT as small as possible
        $payload = array(
            "iss" => $issuer,
            "aud" => $audience,
            "iat" => $issuedAt,
            "nbf" => $notbefore,
            "exp" => $expire,
            "data" => array(
                "id" => $user->id,
               // "fullNmaame" => $user->full_name,
                "email" => $user->email,
                "role" => $user->role,
               // "image" => $user->image
        ));

        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        return 
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "id" => $user->id,
                "firstName" => $user->firstName,
                "lastName" => $user->lastName,
                "email" => $user->email,
                "role" => $user->role,
                "expireAt" => $expire
            );
    } 

    public function registerUser(){

       // read user data from request body
       $postedUser = $this->createObjectFromPostedJson("Models\\User");

       // check if all information of a user is filled
       if (!isset( $postedUser->email) || !isset( $postedUser->password) 
           || !isset( $postedUser->firstName) || !isset( $postedUser->lastName) 
           || !isset( $postedUser->secretCode) || !isset( $postedUser->role)) {
        $this->respondWithError(400, "firstName, lastName, email, password, secretCode,role must be filled ");
        return;
        }

        //check if email is in a correct format
        if (!filter_var($postedUser->email, FILTER_VALIDATE_EMAIL)) {
            $this->respondWithError(401, "invalid email address filled ");
            return;
        }

        //check if role is in correct format
        if ($postedUser->role != "admin" and $postedUser->role != "user") {
            
            $this->respondWithError(400, "role must be filled as admin or user ");
        return;
        }

        // making admin role can be done only by admin this method checks otherwise gives error respons
        if ($postedUser->role == "admin" ) {
            $tocken = $this->checkForJwt();
              if ($tocken==null || $tocken->data->role !=  "admin") {
                  $this->respondWithError(401, "You are not Authorized to creat admin role");
                  return;
                }
            
        }
        
        // this checks if email already exist
        if ($this->service->getUserByEmail($postedUser->email) != null) {
            $this->respondWithError(401, "email already exist");
            return;
        }

       
      // register user to db
       $registerUser =$this->service->registerUser($postedUser);

       //return the resgisterd user
       $this->respond($registerUser);
    
        
    }


    public function update($id)
    {
        try {
         
          $tocken = $this->checkForJwt();
          if ( $tocken->data->id != $id) {
            if ($tocken->data->role !=  "admin") {
                $this->respondWithError(401, "You are not Authorized to change this account data");
                return;
              }
          }
            $postedUser = $this->createObjectFromPostedJson("Models\\User");

             // check if all information of a user is filled
            if (!isset( $postedUser->firstName) || !isset( $postedUser->lastName) 
           || !isset( $postedUser->secretCode)) {
        $this->respondWithError(400, "firstName, lastName, secretCode must be filled ");
        return;
        }
            $user = $this->service->update($postedUser, $id);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($user);
    }

    // this is a way to reset password with the help of secret code
    public function forgetPassword()
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            
             // check if all information of a user is filled
            if (!isset( $postedUser->email) || !isset( $postedUser->password) || !isset( $postedUser->secretCode)) {
         $this->respondWithError(400, "email, password and secretCode must be filled ");
         return;
         }

           // get user by email adress
            $user = $this->service->getUserByEmail($postedUser->email);
           
            //check if a user fill a correct secret code
            if ($user->secretCode != $postedUser->secretCode) {
                $this->respondWithError(404, "your secret code is wrong try again");
                return;
            }

            //change password in db
            $changePassword =$this->service->changePassword($user->id,$postedUser->password);
            $this->respond( $changePassword);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        
    }
    public function getAllUsersNotFriends(){
        //check if a user is updating his post 
        $tocken = $this->checkForJwt();
       if (!$tocken) {
           return;
       } 
       
       $users = $this->service->getAllUsersNotFriends($tocken->data->id);
       if (!$users) {
           $this->respondWithError(404, "user list is empty");
           return;
       }
       $this->respond($users);
       
   }
   
}
