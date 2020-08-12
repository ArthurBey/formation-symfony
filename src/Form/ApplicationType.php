<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType // AbstractType est la classe mère des FormType
{
    /**
     * Retourne la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfiguration($label, $placeholder, $options = []) {  
        return array_merge_recursive([ // _recursive car si jamais on merge avec un tableau d'autres options qui contient aussi par ex 'attr' -> ça supprimera l'ancien attr...
            'label' => $label, 
            'attr' => [
                    'placeholder' => $placeholder
                ]], $options); // Les options ex: required = false...
    }
}