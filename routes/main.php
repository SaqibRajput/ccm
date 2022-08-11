<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware'=>'auth:api'], function ($router) {
        
    $router->post('/catalog/get-price-call', '\CCM\Leads\Controllers\Controller@getPriceAPICall');

});