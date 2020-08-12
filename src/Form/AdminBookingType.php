<?php

namespace App\Form;

use App\Entity\Ad;
use App\Entity\User;
use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AdminBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text' // pour avoir un champ type calendrier
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text' // pour avoir un champ type calendrier
            ])
            ->add('comment')
            ->add('booker', EntityType::class, [
                'class' => User::class,
              //'choice_label' => 'fullName' ->On choisi la propriété de la liste déroulante
                'choice_label' => function($user) {
                    return $user->getFirstName() . " " . strtoupper($user->getLastName());
                }
            ])
            ->add('ad', EntityType::class, [
                'class' => Ad::class,
                'choice_label' => 'title'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }

}
