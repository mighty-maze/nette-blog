<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

class ArticleManager
{
    use SmartObject;

    /** @var Context */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getPublicArticles(): Selection
    {
        return $this->database->table('posts')
            ->where('created_at < ', new \DateTime)
            ->order('created_at DESC');
    }
}