<?php

use illuminate\http\request;
use illuminate\support\facades\route;

/*
|--------------------------------------------------------------------------
| api routes
|--------------------------------------------------------------------------
|
| here is where you can register api routes for your application. these
| routes are loaded by the routeserviceprovider within a group which
| is assigned the "api" middleware group. enjoy building your api!
|
*/

route::middleware('auth:api')->group(function (){

    route::get('/contacts', 'ContactsController@index');
    route::post('/contacts', 'ContactsController@store');
    route::get('/contacts/{contact}', 'ContactsController@show');
    route::patch('/contacts/{contact}', 'ContactsController@update');
    route::delete('/contacts/{contact}', 'ContactsController@destroy');
});


