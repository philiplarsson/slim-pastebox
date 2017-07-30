<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use App\PasteHandler;

class PasteController
{

    protected $view;
    protected $pasteHandler;

    public function __construct(Twig $view, PasteHandler $ph)
    {
        $this->view = $view;
        $this->pasteHandler = $ph;
    }

    public function showPaste(Request $request, Response $response, $args)
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

    public function showPastes(Request $request, Response $response)
    {
        $pasteBoxes = $this->pasteHandler->getPasteBoxes();

        foreach ($pasteBoxes as $paste) {
            $paste->link = $this->getLink($paste->base62);
        }

        return $this->view->render($response, 'pastes.twig', [
            'pastes' => $pasteBoxes
        ]);
    }

    public function createPaste(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $paste = $parsedBody['paste'];

        if (!isset($paste) || empty($paste)) {
            return $this->view->render($response, 'home.twig', [
                'notification' => 'Must have any text in Paste to create a paste. '
            ]);
        }
        $base62 = $this->pasteHandler->createPasteBox($parsedBody['title'], $parsedBody['syntax'], $paste);
        $link = $this->getLink($base62);

        return $this->view->render($response, 'new.twig', [
            'link' => $link
        ]);
    }

    public function showAPI(Request $request, Response $response)
    {
        return $this->view->render($response, 'api.twig');
    }

    public function create(Request $request, Response $response)
    {
        $requestData = $request->getParsedBody();
        if (!$this->requestValid($requestData)) {
            return $response->withJson([
                'errors' => array( [
                                       'status' => 400,
                                       'title'  => 'Invalid request'
                                   ] )
            ], 400);
        }
        $data = $requestData['data'];
        $base62 = $this->pasteHandler->createPasteBox($data['title'], $data['syntax'], $data['paste']);
        $link = $this->getLink($base62);
        return $response->withJson([
            'data' => [
                'type' => 'link',
                'id' => $base62,
                'attributes' => [
                    'url' => $link
                ]
            ]
        ]);
    }

    private function requestValid($requestData)
    {
        /* Basic check to see if data.paste exists in requestData */
        if (array_key_exists('data', $requestData) && array_key_exists('paste', $requestData['data'])) {
            return true;
        }
        return false;
    }

    private function getLink($base62)
    {
        return $_SERVER['HTTP_HOST'] . '/p/' . $base62;
    }
}
