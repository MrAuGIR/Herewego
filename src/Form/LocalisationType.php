<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Localisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LocalisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('cityName', TextType::class,[
                'attr' => ['placeholder' => 'Saisissez le nom de la ville']
            ])
            ->add('cityCp', TextType::class,[
                'attr' => ['placeholder' => 'Saisissez le code postal']
            ])
            ->add('adress',TextType::class,[
                'attr'=>['placeholder'=>'Saisissez l\'adresse']
            ])
            ->add('coordonneesX', TextType::class, [
                'attr'=>['hidden'=>true]
            ])
            ->add('coordonneesY', TextType::class, [
                'attr' => ['hidden' => true]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Localisation::class,
        ]);
    }
}
