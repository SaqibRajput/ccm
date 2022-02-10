<?php

    $router->get('/service-name', function () use ($router) {
        return [$router->app->version(). ' | Its Authentication API Service'];
    });

    $router->group(['middleware' => 'service_access'], function () use ($router) {
        $router->post('/service/get-generic-data', '\CCM\Leads\Controller\Controller@getGenericData');
    });
