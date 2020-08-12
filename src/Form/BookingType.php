<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\ApplicationType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BookingType extends ApplicationType
{
    private $transformer;

    public function __construct(FrenchToDateTimeTransformer $transformer) 
    {
        $this->transformer = $transformer; // On veux utiliser notre DataTransformer qui convertie date fr en date php via injection de dep --> donc dans constructeur
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // à la base :  DateType::class avec une option ["widget" => "single_text"] mais on utilise maintenant bootstrap-datepicker
            // Sinon y'avais les 2 types de calendriers qui s'affichent
            ->add('startDate', TextType::class, $this->getConfiguration("Date d'arrivée", "Votre date d'arrivée"))
            ->add('endDate', TextType::class, $this->getConfiguration("Date de départ", "Votre date de départ"))
            ->add('comment', TextareaType::class, $this->getConfiguration(false , "Laissez une remarque particulière pour votre réservation", ["required" => false]));
        ;

        $builder->get('startDate')->addModelTransformer($this->transformer);
        $builder->get('endDate')->addModelTransformer($this->transformer);
    }
 
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'validation_groups' => ['Default', 'front'] // On ajoute les validation Groups vis à vis des assert des startDate et endDate
        ]);
    }
}
