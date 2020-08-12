<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings", name="admin_bookings_index")
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Booking::class);

        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repository->findAll()
        ]);
    }

    /**
     * Editer une résa
     * 
     * @Route("/admin/bookings/{id}/edit", name="admin_booking_edit")
     *
     * @return Response
     */
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager) 
    {
        $form = $this->createForm(AdminBookingType::class, $booking, [
            'validation_groups' => ["Default", "front"] // default pour qu'il applique l'assert du endDate qui doit etre supérieur à date d'arrivée
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
        //  $booking->setAmount($booking->getAd()->getPrice() * $booking->getDuration()); // getDuration() fonction custom qui retourne nb de jours de la résa
            $booking->setAmount(0);
            $manager->persist($booking); // En vrai on peut se passer du persist, il s'agit juste de modifier ici une annonce qui existe deja, flush suffit
            $manager->flush();

            $this->addFlash(
                'success',
                "La réservation n°{$booking->getId()} a bien été modifiée"
            );

            return $this->redirectToRoute("admin_bookings_index");
        }
        return $this->render('admin/booking/edit.html.twig', [
            'form' => $form->createView(),
            'booking' => $booking
        ]);
    }

    /**
     * Permet de supprimer résa
     *
     * @Route("/admin/bookings/{id}/delete", name="admin_booking_delete")
     * 
     * @return Response
     */
    public function delete(Booking $booking, EntityManagerInterface $manager) {
        $manager->remove($booking);
        $manager->flush();

        $this->addFlash(
            'success',
            "La réservation a bien été supprimée"
        );

        return $this->redirectToRoute("admin_bookings_index");
    }
}
