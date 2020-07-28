<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController{

    /**
     * Undocumented function
     * @Route("/hello/{prenom}/age/{age}", name="hello")
     * @Route("/hello", name="hello_base")
     * @Route("/hello/{prenom}", name="hello_prenom")
     * @return void
     */
    public function hello($prenom = "annonyme", $age = "0") {
        return $this->render(
            'hello.html.twig',
            [
                'prenom' => $prenom,
                'age' => $age
            ]
            );
    }

    /**
     * Undocumented function
     * @Route("/", name="homepage")
     * @return void
     */
    public function home(){
        return $this->render(
            'home.html.twig'

        );
    }
} 

?>