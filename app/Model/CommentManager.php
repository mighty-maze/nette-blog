<?php


namespace App\Model;

use Nette\Database\Context;
use Nette\SmartObject;

class CommentManager
{
    use SmartObject;

    /** @var Context */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getApprovedCommentsByPost(int $postId)
    {
        return $this->database->table('comments')
            ->where('status','approved')
            ->where("post_id", $postId);
    }
}