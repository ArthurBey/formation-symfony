<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(Ad $ad, Request $request, EntityManagerInterface $manager)
    {
        $booking = new Booking;
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // amount et createdAt -> on les fait déterminer dans l'entité Booking elle meme avec une LifecycleCallback + PrePersist
            $user = $this->getUser();
            $booking->setBooker($user)
                    ->setAd($ad);
            
            // Si les dates ne sont pas dispo, msg d'erreur
            if(!$booking->isBookableDates()){
                $this->addFlash(
                    'warning',
                    "Les dates que vous avez choisi sont déjà réservées"
                );
            } else {
                $manager->persist($booking);
                $manager->flush();

                return $this->redirectToRoute('booking_show', [
                    'id' => $booking->getId(),
                    'confirmed' => true
                ]);
            }
        }
        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Afficher la page d'une confirmation de réservation + form pour laisser un avis
     * @Route("/booking/{id}", name="booking_show")
     *
     * @param Booking $booking
     * @param Request $request
     * @param Manager $manager
     * @return Response
     */
    public function show(Booking $booking, Request $request, EntityManagerInterface $manager) {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $comment->setAd($booking->getAd())
                    ->setAuthor($this->getUser());
            
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre avis a bien été publié et dévoilé au grand jour !'
            );
        }

        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
            'form'    => $form->createView()
        ]);
    }
}