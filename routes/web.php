<?php

use App\Controllers\PasteController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'home.twig');
});

$app->get('/p/{base62}', PasteController::class . ':index');
