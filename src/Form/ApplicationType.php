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
        return array_merge([
            'label' => $label, 
            'attr' => [
                    'placeholder' => $placeholder
                ]], $options); // Les options ex: required = false...
    }
}