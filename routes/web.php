<?php

use App\Controllers\PasteController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'home.twig');
})->setName('home');

$app->get('/pastes', PasteController::class . ':showPastes')->setName('showPastes');
$app->get('/p/{base62}', PasteController::class . ':showPaste');
$app->get('/api', PasteController::class . ':showAPI')->setName('showAPI');
$app->get('/deletePaste/{base62}', PasteController::class . ':deletePaste')->setName('deletePaste');
$app->post('/createPaste', PasteController::class . ':createPaste')->setName('createPaste');
