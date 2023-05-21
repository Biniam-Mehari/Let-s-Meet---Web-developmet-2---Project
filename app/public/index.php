<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');

// routes for the users endpoint
$router->post('/users/login', 'UserController@login');//with out tocken check
$router->post('/users', 'UserController@registerUser'); //with out tocken check
$router->put('/users/(\d+)', 'UserController@update');
$router->put('/users/forgetpassword', 'UserController@forgetPassword'); //with out tocken check

// routes for the posts endpoint
$router->get('/posts', 'PostController@getAll');
$router->get('/posts/(\d+)', 'PostController@getOne');
$router->post('/posts', 'PostController@create');
$router->put('/posts/update/(\d+)', 'PostController@update');
$router->put('/posts/block/(\d+)', 'PostController@blockPost');
$router->delete('/posts/(\d+)', 'PostController@delete');
//$router->get('/posts/ByUserId/(\d+)', 'PostController@getPostByUserId');

// routes for the friends endpoint
$router->get('/categories', 'CategoryController@getAll');
$router->get('/friends/(\d+)', 'FriendsController@getAllMyFriends');
$router->post('/friends', 'FriendsController@create');
$router->put('/friends/(\d+)', 'FriendsController@update');
$router->delete('/friends/(\d+)', 'FriendsController@delete');

// routes for the messages endpoint
$router->get('/messages/(\d+)', 'MessageController@getfriendsSentOrReceiveMessage');
$router->get('/messages', 'MessageController@getOneConversation');
$router->post('/messages', 'MessageController@create');
$router->delete('/messages/(\d+)', 'MessageController@delete');

// Run it!
$router->run();