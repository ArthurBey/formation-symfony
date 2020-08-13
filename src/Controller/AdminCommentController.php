<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\Pagination;
use App\Form\AdminCommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    /**
     * @Route("/admin/comments/{page<\d+>?1}", name="admin_comments_index")
     */
    public function index($page, Pagination $pagination)
    {   
        // On indique juste l'entité et la page actuelle
        $pagination->setEntityClass(Comment::class)
                   ->setRoute('admin_comments_index') // on indique la route des liens cliquables de pagination
                   ->setLimit(5) // ici on veut 5 comments par page au lieu de 10 par défaut
                   ->setPage($page);

        return $this->render('admin/comment/index.html.twig', [
            'pagination' => $pagination // On laisse twig extraire les infos
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