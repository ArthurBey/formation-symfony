<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdType extends AbstractType
{
    /**
     * Retourne la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = []) {
        return array_merge([
            'label' => $label, 
            'attr' => [
                    'placeholder' => $placeholder
                ]], $options); // Les options ex: required = false...
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 
                  TextType::class, 
                  $this->getConfiguration('Titre', 'Entrez un titre'))
            ->add('slug', 
                  TextType::class, 
                  $this->getConfiguration('Chaine URL', 'Adresse web (auto)', [
                      'required' => false // On veut pas que le slug soit obligatoire !
                    ])
                )
            ->add('introduction', 
                  TextType::class, 
                  $this->getConfiguration("Introduction", "Donnez une description globale de l'annonce"))
            ->add('content', 
                  TextareaType::class, 
                  $this->getConfiguration("Description", "Donnez une description détaillée"))
            ->add('rooms',  
                  IntegerType::class, 
                  $this->getConfiguration('Nombre de chambres', 'Nombre de chambres'))
            ->add('price', 
                  MoneyType::class, 
                  $this->getConfiguration('Prix par nuit', 'Indiquez le prix'))
            ->add('coverImage', 
                  UrlType::class, 
                  $this->getConfiguration('URL', 'URL de l\'image'))
            ->add('images',
                  CollectionType::class,
                  [ 
                    'entry_type' => ImageType::class,
                    'allow_add' => true, // On permet d'ajouter des nouveaux elements
                    'allow_delete' => true
                  ]);
            /* ->add('save', 
                  SubmitType::class, 
                  [ "attr" => ['class' => 'btn btn-primary'],
                    'label' => "Créer l'annonce"
                  ]); */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
