<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * @Route("/admin/comments", name="admin_comments_index")
     */
    public function index()
    {   // ou par injection de dep en arg : CommentRepository
        $repository = $this->getDoctrine()->getRepository(Comment::class); 
        $comments = $repository->findAll();
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments
        ]);

    }

    /**
     * Permet de modifier un commentaire
     *
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     * 
     * @return Response
     */
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager) {
        $form = $this->createForm(AdminCommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Commentaire {$comment->getId()} modifié avec succès" 
            );
        }

        return $this->render('admin/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    /**
     * Supprimer commentaire
     *
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     * 
     * @return Response
     */
    public function delete(Comment $comment, EntityManagerInterface $manager){
        $commentId = $comment->getId();

        $manager->remove($comment);
        $manager->flush();

        if(!$comment->getId()){
            $this->addFlash(
                    'success',
                    "Commentaire n°{$commentId} supprimé"
                );
        }
    
        return $this->redirectToRoute("admin_comments_index");
    }
}