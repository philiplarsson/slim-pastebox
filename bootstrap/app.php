<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = include_once __DIR__ . '/../config/app-config.php';

$app = new \Slim\App([ 'settings' => $config ]);

$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
            'cache' => false,
            'debug' => true
        ]
    );

    // Instantiate and add Slim specific extensions
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};


/* Load routes */
require_once  __DIR__ . '/../routes/web.php';
