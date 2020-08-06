<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

// Ce Transformer servira à transformer date FR en date PHP
// Tout les Transformers doivent implémenter cette interface
class FrenchToDateTimeTransformer implements DataTransformerInterface
{
    public function transform($date)
    {
        if($date === null) {
            return '';
        }
        return $date->format('d/m/Y');
    }

    public function reverseTransform($frenchDate)
    {
        // frenchDate = 21/09/2020
        if($frenchDate === null) {
            throw new TransformationFailedException('On attend une date');
        }

        $date = \DateTime::createFromFormat('d/m/Y', $frenchDate); // On utilise la méthode statique de DT pour créer un format de date spécifié en 1er arg
        if($date === false) { // ex si mauvais format donc createFromFormat renvoi null
            throw new TransformationFailedException('Pas le bon format de date');
        }

        return $date;
    }
}