<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = include_once __DIR__ . '/../config/app-config.php';

$app = new \Slim\App([ 'settings' => $config ]);

$container = $app->getContainer();

/* Register view in container */
$container["view"] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
            'cache' => false,
            'debug' => true
        ]
    );

    // Instantiate and add Slim specific extensions
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    $view->addExtension(new Twig_Extension_Debug());
    return $view;
};

/* Register PDO in container */
$container["db"] = function ($c) {
    $pdo = new PDO("sqlite:" . __DIR__ . '/../database/database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

/* Register Controllers */
$container["App\Controllers\PasteController"] = function ($c) {
    $view = $c->view;
    $ph = new \App\PasteHandler($c->db);
    return new \App\Controllers\PasteController($view, $ph);
};

/* Load routes */
require_once  __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/api.php';
