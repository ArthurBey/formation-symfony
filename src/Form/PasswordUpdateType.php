<?php

namespace App\Form;

use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

// Généré sans le faire lié à l'entité PasswordUpdate vu que pas une entité car pas d'ORM
class PasswordUpdateType extends ApplicationType // à la base 'extends AbstractType, mais là c'est notre classe pour la méthode custom getConfiguration()
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, $this->getConfiguration("Ancien mot de passe", "Entrez votre mot de passe actuel"))
            ->add('newPassword', PasswordType::class, $this->getConfiguration("Nouveau mot de passe", "Tapez votre nouveau mot de passe"))
            ->add('confirmPassword', PasswordType::class, $this->getConfiguration("Répetez le mdp", "Répetez le mdp"));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
