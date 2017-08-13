<?php

namespace App;

use App\Controllers\PasteController;
use App\Math as Math;
use App\Models\PasteBox;

class PasteHandler
{
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createPasteBox($paste, $title = '', $syntax = '', $expiresDate = null)
    {
        $pasteId = $this->createPaste($paste);
        $base62 = Math::toBase($pasteId);
        $date = new \DateTime('now', new \DateTimeZone(PasteController::TIMEZONE));
        $currentDate = $date->format('Y-m-d H:i');

        if (isset($expiresDate)) {
            $expiresDate = $expiresDate->format('Y-m-d H:i');
        }
        $sql = "INSERT INTO pastebox (paste_id, title, syntax, base62, created_at, expire_date)" .
            "VALUES (:paste_id, :title, :syntax, :base62, :created_at, :expires_date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':paste_id'   => $pasteId,
            ':title'      => $title,
            ':syntax'     => $syntax,
            ':base62'     => $base62,
            ':created_at' => $currentDate,
            ':expires_date' => $expiresDate
        ]);

        return $base62;
    }

    private function createPaste($paste)
    {
        $sql = "INSERT INTO pastes (paste) VALUES (:paste)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ ':paste' => $paste ]);

        return $this->pdo->lastInsertId();
    }

    public function getPasteBox($base62)
    {
        $sql = "SELECT * FROM pastebox WHERE base62 = :base62";
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, PasteBox::class);
        $stmt->execute([ ':base62' => $base62 ]);
        $pasteBox = $stmt->fetch();
        if ($pasteBox === false) {
            return null;
        }
        $paste = $this->getPaste($pasteBox->paste_id);
        $pasteBox->paste = $paste;

        return $pasteBox;
    }

    public function getPasteBoxes($limit = 25)
    {
        $sql = "SELECT * FROM pastebox ORDER BY id DESC LIMIT :limit, :pastesPerPage";
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, PasteBox::class);
        $stmt->execute([
            ':limit' => $limit,
            ':pastesPerPage' => PasteController::PASTE_PER_PAGE
        ]);
        $pasteBoxes = $stmt->fetchAll();
        if ($pasteBoxes === false) {
            return null;
        }
        foreach ($pasteBoxes as $pasteBox) {
            $paste = $this->getPaste($pasteBox->paste_id);
            $pasteBox->paste = $paste;
        }

        return $pasteBoxes;
    }

    public function getPaste($pasteId)
    {
        $sql = "SELECT * FROM pastes WHERE id = :paste_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute([ ':paste_id' => $pasteId ]);
        $paste = $stmt->fetch();

        return $paste['paste'];
    }

    public function delete($base62)
    {
        $pasteBox = $this->getPasteBox($base62);
        $this->deletePaste($pasteBox->paste_id);
        $this->deletePasteBox($base62);
    }

    private function deletePaste($pasteId)
    {
        $sql = "DELETE FROM pastes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ ':id' => $pasteId ]);
    }

    private function deletePasteBox($base62)
    {
        $sql = "DELETE FROM pastebox WHERE base62 = :base62";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([ ':base62' => $base62 ]);
    }

    public function getNbrOfPastes()
    {
        $sql = 'SELECT COUNT(*) FROM PASTES';
        $q = $this->pdo->query($sql);
        return intval(current($q->fetch(\PDO::FETCH_ASSOC)));
    }
}
