<?php

namespace App\Form;

use App\Entity\Localisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocalisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('cityName', TextType::class, [
                'attr' => [
                    'placeholder' => '',
                    'readonly' => true,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La ville est obligatoire',
                    ]),
                ],
                'required' => false,
            ])
            ->add('cityCp', TextType::class, [
                'attr' => [
                    'placeholder' => '',
                    'readonly' => true,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire',
                    ]),
                ],
                'required' => false,

            ])
            ->add('adress', TextType::class, [
                'attr' => [
                    'placeholder' => '',
                    'readonly' => true,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "L'adresse complète est obligatoire",
                    ]),
                ],
                'required' => false,
            ])
            ->add('coordonneesX', TextType::class, [
                'label' => false,
                'attr' => ['hidden' => true],
                'required' => false,
            ])
            ->add('coordonneesY', TextType::class, [
                'label' => false,
                'attr' => ['hidden' => true],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Localisation::class,
        ]);
    }
}
