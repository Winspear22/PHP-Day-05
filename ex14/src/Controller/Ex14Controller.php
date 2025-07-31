<?php

namespace App\Controller;

use Throwable;
use Doctrine\DBAL\Connection;
use App\Service\TableCreatorService;
use App\Service\CommentInsertService;
use App\Service\CommentReadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex14Controller extends AbstractController
{
    /**
     * @Route("/ex14", name="ex14_index")
     */
    /*public function index(Connection $connection,
        CommentReadService $readService,
        TableCreatorService $tableCreatorService): Response
    {
        $comments = [];
        try 
        {
            $doesTableExist = $tableCreatorService->checkTableExistence($connection, 'ex14_comments');
            if (!$doesTableExist) {
                $this->addFlash('info', 'Table does not exist. Please create it first.');
                $this->addFlash('info', 'Please, create it first.');
                return $this->render('ex14/index.html.twig', [
                    'comments' => $comments,
                ]);
            }
            $comments = [];
            if ($doesTableExist)
                $comments = $readService->getAllComments($connection, 'ex14_comments');
            $this->addFlash('success', 'Table exists.');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, we cannot display the comment list : '.$e->getMessage());
        }
            return $this->render('ex14/index.html.twig', [
                'comments' => $comments,
            ]);
    }*/

    /**
     * @Route("/ex14", name="ex14_index")
     */
    public function index(
    Connection $connection,
    CommentReadService $readService,
    TableCreatorService $tableCreatorService
    ): Response
    {
        $comments = [];
        $tableExists = false;
        try
        {
            $tableExists = $tableCreatorService->checkTableExistence($connection, 'ex14_comments');
            if ($tableExists)
                $comments = $readService->getAllComments($connection, 'ex14_comments');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, we cannot display the comment list: '.$e->getMessage());
        }

        return $this->render('ex14/index.html.twig', [
            'comments'    => $comments,
            'tableExists' => $tableExists,
            'tableName'   => 'ex14_comments'
        ]);
    }


    /**
     * @Route("/ex14/create_table", name="ex14_create_table", methods={"POST"})
     */
    public function createTable(TableCreatorService $tableCreator, Connection $connection): Response
    {
        try
        {
            $result = $tableCreator->createTable($connection, 'ex14_comments');
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
            return $this->redirectToRoute('ex14_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error creating table: ' . $e->getMessage());
            return $this->redirectToRoute('ex14_index');
        }
    }

    /**
     * @Route("/ex14/insert_vulnerable_comment", name="ex14_insert_vulnerable_comment", methods={"POST"})
     */
    public function insertVulnerableComment(CommentInsertService $commentInsert, 
    Connection $connection,
    Request $request,
    CommentReadService $readService): Response
    {
        try
        {
            $comment = $request->request->get('comment', '');
            if ($error = $readService->validateComment($comment, 64))
            {
                $this->addFlash('danger', $error);
                return $this->redirectToRoute('ex14_index');
            }
            $result = $commentInsert->insertCommentVulnerable($connection, 'ex14_comments', $comment);

            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);

            return $this->redirectToRoute('ex14_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, we could not insert your vulnerable comment : ' . $e->getMessage());
            return $this->redirectToRoute('ex14_index');
        }
    }

    /**
     * @Route("/ex14/insert_secure_comment", name="ex14_insert_secure_comment", methods={"POST"})
     */
    public function insertSecureComment(CommentInsertService $commentInsert, 
    Connection $connection,
    Request $request,
    CommentReadService $readService): Response
    {

        try
        {
            $comment = $request->request->get('comment', '');
            if ($error = $readService->validateComment($comment, 64))
            {
                $this->addFlash('danger', $error);
                return $this->redirectToRoute('ex14_index');
            }
            $result = $commentInsert->insertCommentSafe($connection, 'ex14_comments', $comment);

            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);

            return $this->redirectToRoute('ex14_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, we could not insert your secured comment : ' . $e->getMessage());
            return $this->redirectToRoute('ex14_index');
        }
    }

}
