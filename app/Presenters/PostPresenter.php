<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\ArticleManager;
use App\Model\CommentManager;
use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{
    /** @var ArticleManager */
    private $articleManager;
    /**
     * @var CommentManager
     */
    private $commentManager;

    public function __construct(ArticleManager $articleManager, CommentManager $commentManager)
    {
        $this->articleManager = $articleManager;
        $this->commentManager = $commentManager;
    }

    public function renderShow(int $postId): void
    {
        $post = $this->articleManager->getPublicArticles()->get($postId);
        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->post = $post;
        $this->template->comments = $this->commentManager->getApprovedCommentsByPost($postId);
    }

    protected function createComponentCommentForm(): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Jméno:')
            ->setRequired();

        $form->addEmail('email', 'E-mail:');

        $form->addTextArea('content', 'Komentář:')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

    public function commentFormSucceeded(Form $form, \stdClass $values): void
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
            'status' => 'new',
        ]);

        $this->flashMessage('Děkuji za komentář. Uvidíte ho na stránkách hned po schválení administrátorem.', 'success');
        $this->redirect('this');
    }

    protected function createComponentPostForm(): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('title', 'Titulek:')
            ->setRequired();

        $form->addTextArea('content', 'Obsah:')
            ->setRequired();

        $form->addSubmit('send', 'Uložit a publikovat');

        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    public function postFormSucceeded(Form $form, array $values): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }

        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }


        $this->flashMessage('Příspěvek byl úspěšně publikován', 'success');
        $this->redirect('show', $post->id);
    }

    public function actionEdit(int $postId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }
        $this['postForm']->setDefaults($post->toArray());
    }


    public function actionCreate(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function renderCreate()
    {

    }

}

