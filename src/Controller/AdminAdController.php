<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     * \d+ signifie un ou plusieurs nombres & le ? signifie param optionnel, 1 val par défaut => inlined requirement
     */
    public function index($page, Pagination $pagination) // page = 1 plus nécessaire grace à la inlined requirement en annotation "?1" 
    {
        // On indique juste l'entité et la page actuelle
        $pagination->setEntityClass(Ad::class)
                   ->setRoute('admin_ads_index') // on indique la route des liens cliquables de pagination
                   ->setLimit(8) // 8 au lieu du default de 10
                   ->setPage($page);

        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $pagination // On laisse twig extraire les infos
        ]);
    }

    /**
     * Retourne/gère formulaire d'édition d'une annonce
     * Note: ici on utilise 'id' dans la route alors que edit du AdController on utilise slug, slug pour donner un sens au url pas primordial...
     * 
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * 
     * @param Ad $ad
     * @return Response
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdType::class, $ad); // On réutilise simplement le AdType qui permet aussi à un utilisateur de modif son annonce

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée !"
            );
        }
        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("admin/ads/{id}/delete", name="admin_ads_delete")
     * 
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager) {
        if(count($ad->getBookings()) > 0){ // Sans ce if, erreur fatale si l'annonce a des résas car les résas sont dépendante des annonces (FK..)
            $this->addFlash(
                'warning',
                "Suppression de l'annonce <strong>{$ad->getTitle()}</strong> impossible ! Des réservations y sont liés"
            );
        } else {
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
            );
        }
        
        return $this->redirectToRoute('admin_ads_index');
    }

}
