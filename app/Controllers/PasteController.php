<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;
use App\PasteHandler;

class PasteController
{

    protected $view;
    protected $pasteHandler;
    protected $router;
    const PASTE_PER_PAGE = 15;
    const TIMEZONE = 'Europe/Stockholm';

    public function __construct(Twig $view, PasteHandler $ph, Router $router)
    {
        $this->view = $view;
        $this->pasteHandler = $ph;
        $this->router = $router;
    }

    public function showPaste(Request $request, Response $response, $args)
    {
        $base62 = $args['base62'];
        $pasteBox = $this->pasteHandler->getPasteBox($base62);
        if (!isset($pasteBox)) {
            return $this->view->render($response, 'paste.twig');
        }

        $pasteBox->link = $this->getLink($pasteBox->base62);
        return $this->view->render($response, 'paste.twig', [
            'pasteBox' => $pasteBox
        ]);
    }

    public function showPastes(Request $request, Response $response)
    {
        $nbrOfPastes = $this->pasteHandler->getNbrOfPastes();
        $nbrOfPages = ceil($nbrOfPastes/PasteController::PASTE_PER_PAGE);
        $currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

        /* Make sure currentPage is set and between sane values */
        if (!isset($currentPage) ||
            $currentPage < 1 ||
            $currentPage > $nbrOfPages) {
            $currentPage = 1;
        }
        $pasteBoxes = $this->pasteHandler->getPasteBoxes(($currentPage - 1) * PasteController::PASTE_PER_PAGE);
        foreach ($pasteBoxes as $paste) {
            $paste->link = $this->getLink($paste->base62);
        }

        $pagesToShow = [];
        foreach (range(1, $nbrOfPages) as $page) {
            /** Show first, last and one between each limit */
            if ($page == 1 ||
                $page == $nbrOfPages ||
                $page == $currentPage ||
                ($page >= $currentPage -1 && $page <= $currentPage +1)) {
                $pagesToShow[] = $page;
            }
        }
        return $this->view->render($response, 'pastes.twig', [
            'pastes' => $pasteBoxes,
            'currentPage' => $currentPage,
            'pages' => $pagesToShow
        ]);
    }

    public function createPaste(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $paste = $parsedBody['paste'];
        $expires = $parsedBody['expires'];
        if ($expires == 'never') {
            $expiresDate = null;
        } else {
            $expiresDate = $this->getExpiresDate($expires);
        }
        if (!isset($paste) || empty($paste)) {
            return $this->view->render($response, 'home.twig', [
                'notification' => "Field 'Paste' is required. "
            ]);
        }
        $base62 = $this->pasteHandler->createPasteBox($paste, $parsedBody['title'], $parsedBody['syntax'], $expiresDate);
        $link = $this->getLink($base62);

        return $this->view->render($response, 'new.twig', [
            'link' => $link
        ]);
    }

    public function deletePaste(Request $request, Response $response, $args)
    {
        $base62 = $args['base62'];

        $this->pasteHandler->delete($base62);
        return  $response->withRedirect($this->router->pathFor('showPastes'));
    }

    public function showAPI(Request $request, Response $response)
    {
        return $this->view->render($response, 'api.twig');
    }

    public function about(Request $request, Response $response)
    {
        return $this->view->render($response, 'about.twig');
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

    /**
     * Returns a DateTime object with added time.
     *
     * @param string $expires should be of any time that are specified
     *                        here http://www.php.net/datetime.formats.relative
     * @return \DateTime object with expire date
     */
    private function getExpiresDate(string $expires)
    {
        $expireDate = new \DateTime('now', new \DateTimeZone(PasteController::TIMEZONE));
        $expireDate->modify($expires);
        if (!$expireDate) {
            throw new \InvalidArgumentException($expires . ' is not a valid argument to getExpiresDate. ');
        }
        return $expireDate;
    }
}
