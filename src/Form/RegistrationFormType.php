<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationFormType extends ApplicationType
{
    /**
     * Retourne la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    /* private function getConfiguration($label, $placeholder, $options = []) { // DRY : On retrouve cette méthode iddentique sur AdType et ici ! Dans tuto : 
        return array_merge([                                                 // Dans le tuto : On créer une classe "ApplicationType" (avec la méthode qui est protected) qui extends AbstractType, et du coup ici faire "extends ApplicationType"..
            'label' => $label, 
            'attr' => [
                    'placeholder' => $placeholder
                ]], $options); // Les options ex: required = false...
    } */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class,  $this->getConfiguration("Email", "Votre e-mail"))
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'invalid_message' => "Les mots de passes ne correspondent pas",
                'options' => ['attr' => ['class' => 'password-field', 'placeholder' => 'mot de passe...']],
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Repetez le mdp'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom"))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom"))
            ->add('introduction', TextType::class, $this->getConfiguration("Intorduction", "Présentez-vous en quelques mots"))
            ->add('picture', UrlType::class, $this->getConfiguration("Photo de profile (url)", "URL"))
            ->add('description', TextareaType::class, $this->getConfiguration("Description", "Présentez-vous en plus long"))
             ->add('save', 
                  SubmitType::class, 
                  [ "attr" => ['class' => 'btn btn-primary'],
                    'label' => "Créer mon compte"
                  ]); 
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
