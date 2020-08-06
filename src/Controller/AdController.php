<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads ", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        return $this->render('ad/index.html.twig', [
            'ads' => $repo->findAll()
        ]); 
    }

     /**
     * Créer une annonce
     * @Route("ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager){
        $ad = new Ad();
        
        $form = $this->createForm(AdType::class, $ad);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }
            $ad->setAuthor($this->getUser()); // Sinon erreure SQL car "author_id" cannot be null...
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success', // appellé comme ça pour la classe bootstrap qu'on va interpoller dans twig (autre: info, danger, warning)
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }
        
        return $this->render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Editer une annonce via un form d'edition
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()")
     * @return Response
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager){ // On recevra le bon Ad grace au ParamConverter
        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success', // appellé comme ça pour la classe bootstrap qu'on va interpoller dans twig (autre: info, danger, warning)
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée !"
            );

            return $this->redirectToRoute('ads_show', [
                'slug' => $ad->getSlug()
            ]);
        }
        
        return $this->render('ad/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Afficher une annonce
     * @Route("/ads/{slug}", name="ads_show")
     * 
     * @return Response
     */
    public function show(Ad $ad = null){
        if(!$ad) {
            return $this->redirectToRoute('ads_index'); //Sinon erreur symfony
        }
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     * 
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()")
     * 
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager){
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()} a bien été supprimée"
        );

        return $this->redirectToRoute("ads_index");
    }

    
}
