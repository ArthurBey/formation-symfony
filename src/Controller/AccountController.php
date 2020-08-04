<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'last_username' => $lastUsername, 
            'hasError' => $error !== null // 
            ]);
    }

    /**
     * @Route("/logout", name="account_logout")
     * 
     * @return void
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Permet d'afficher et traiter le formulaire de modif du profile
     * 
     * @Route("/account/profile", name="account_profile")
     * @return Response
     */
    public function profile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les données du profil ont été enregistrées avec succès !"
            );
        }
        
        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     *
     * @Route("/account/password-update", name="account_password")
     * 
     * @return Response
     */
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder) {
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser(); // On a besoin de l'entité user notamment pour vérif oldPassword
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) { //isValid vérifie si Assert sont respectées mais oldPassword n'est pas vérifié si bien le oldPassword !
            //On vérifie que le mdp entré dans le formulaire est bien celui de l'utilisateur, 2arg: le hash du mdp !
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                    // si mdp entré n'est pas le bon :  Il faut qu'on renvoie nous meme un msg d'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe entré n'est pas votre mdp actuel"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $password = $encoder->encodePassword($user, $newPassword); //mdp hashé

                $user->setPassword($password);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié !'
                );

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le profil d'utilisateur connecté
     * @Route("/account", name="account_index")
     * 
     * @return Response
     */
    public function myAccount() {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
