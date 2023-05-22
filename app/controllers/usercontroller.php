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

       if (!isset( $postedUser->email) || !isset( $postedUser->password) 
           || !isset( $postedUser->firstName) || !isset( $postedUser->lastName) 
           || !isset( $postedUser->secretCode) || !isset( $postedUser->role)) {
        $this->respondWithError(400, "firstName, lastName, email, password, secretCode,role must be filled ");
        return;
        }

        // if ($postedUser->role != "admin" || $postedUser->role != "user") {
        //     $this->respondWithError(400, "role must be filled as admin or user ");
        // return;
        // }

        if ($postedUser->role == "admin" ) {
            $tocken = $this->checkForJwt();
              if ($tocken==null ) {
                  $this->respondWithError(400, "You are not Authorized to creat admin role");
                  return;
                }
            
        }

       
      // register user to db
       $registerUser =$this->service->registerUser($postedUser);

       return  $registerUser;
    
        
    }


    public function update($id)
    {
        try {
          $admin ="admin";
          $tocken = $this->checkForJwt();
          if ( $tocken->data->id != $id) {
            if ($tocken->data->role !=  $admin) {
                $this->respondWithError(401, "You are not Authorized to change this account data");
                return;
              }
          }
            $user = $this->createObjectFromPostedJson("Models\\User");
            $user = $this->service->update($user, $id);

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
            
            $user = $this->service->getSecretCodeByEmail($postedUser->email);
           
            if ($user->secretCode != $postedUser->secretCode) {
                $this->respondWithError(404, "your secret code is wrong try again");
                return;
            }

            $changePassword =$this->service->changePassword($user->id,$postedUser->password);
            $this->respond( $changePassword);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        
    }
}
