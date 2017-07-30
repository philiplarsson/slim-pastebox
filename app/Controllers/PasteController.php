<?php

namespace App\Controllers;

use Slim\Views\Twig;
use App\PasteHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PasteController
{

    protected $view;
    protected $pasteHandler;

    public function __construct(Twig $view, PasteHandler $ph)
    {
        $this->view = $view;
        $this->pasteHandler = $ph;
    }

    public function index(Request $request, Response $response, $args)
    {
        $base62 = $args['base62'];
        $pasteBox = $this->pasteHandler->getPasteBox($base62);
        if (!isset($pasteBox)) {
            return $this->view->render($response, 'paste.twig');
        }

        return $this->view->render($response, 'paste.twig', [
            'pasteBox' => $pasteBox
        ]);
    }

    public function create(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $paste = $parsedBody['paste'];
        if (!isset($paste)) {
            // TODO: redirect
        }
        $base62 = $this->pasteHandler->createPasteBox($parsedBody['title'], $parsedBody['syntax'], $paste);
        $link = $this->getLink($base62);

        return $this->view->render($response, 'new.twig', [
            'link' => $link
        ]);
    }

    private function getLink($base62)
    {
        return $_SERVER['HTTP_HOST'] . '/p/' . $base62;
    }
}
