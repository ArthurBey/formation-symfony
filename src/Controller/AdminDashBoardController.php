<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashBoardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(EntityManagerInterface $manager, StatsService $statsService)
    {
        // Toute la logique des statistiques (incluant communications DQL) externalisée dans un service
        $users      = $statsService->getUsersCount();
        $ads        = $statsService->getAdsCount();
        $bookings   = $statsService->getBookingsCount();
        $comments   = $statsService->getCommentsCount();

        $bestAds  = $statsService->getAdsStats('DESC');
        $worstAds = $statsService->getAdsStats('ASC');

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => compact('users', 'ads', 'bookings', 'comments'), 
            'bestAds' => $bestAds,
            'worstAds' => $worstAds
        ]);
    }
}
