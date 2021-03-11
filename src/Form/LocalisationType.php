<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Localisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LocalisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('cityName', TextType::class, [
                'attr' => [
                    'placeholder' => "",
                    'readonly' => true
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La ville est obligatoire',
                    ]),
                ]
            ])
            ->add('cityCp', TextType::class, [
                'attr' => [
                    'placeholder' => "",
                    'readonly' => true
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire',
                    ]),
                ]
            ])
            ->add('adress', TextType::class, [
                'attr' => [
                    'placeholder' => "",
                    'readonly' => true
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "L'adresse complète est obligatoire",
                    ]),
                ]
            ])
            ->add('coordonneesX', TextType::class, [
                'label' => false,
                'attr' => ['hidden' => true]
            ])
            ->add('coordonneesY', TextType::class, [
                'label' => false,
                'attr' => ['hidden' => true]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Localisation::class,
        ]);
    }
}
