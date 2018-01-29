<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/v1'], function($router) {
    // commenting on a post
    $router->post("posts/{id}/comments", "CommentsController@post");
    // get post comments
    $router->get("posts/{id}/comments", "CommentsController@getByPostId");
    // get user comments
    $router->get("users/{id}/comments", "CommentsController@getByUserId");
    // get user notifications 
    $router->get("users/{id}/notifications", "NotificationsController@getByUserId");
    // remove a comment
    $router->delete("comments/{id}", ["middleware" => "auth", "uses" => "CommentsController@delete"]);
    // romove all user comments
    $router->delete("posts/{postid}/users/{userid}/comments", ["middleware" => "auth", "uses" => "CommentsController@deleteUserComments"]);
    // requet a token to further authentication
    $router->post('tokens', 'AccountsController@requestToken');
});