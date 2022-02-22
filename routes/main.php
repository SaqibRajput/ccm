<?php

    $router->get('/service-name', function () use ($router) {
        return [$router->app->version(). ' | Its Authentication API Service'];
    });

    $router->group(['middleware' => 'service_access'], function () use ($router) {
        $router->post('/service/get-service-data', '\CCM\Leads\Controller\Controller@getServiceData');
        $router->post('/service/update-service-data', '\CCM\Leads\Controller\Controller@updateServiceData');
        $router->post('/service/create-service-data', '\CCM\Leads\Controller\Controller@createServiceData');
        $router->post('/service/delete-service-data', '\CCM\Leads\Controller\Controller@deleteServiceData');
    });
