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
            ->add('city', EntityType::class, [
                'help' => 'nom ville',
                'class' => City::class,
                'choice_label' => function (City $city) {
                    return ucfirst($city->getCityName());
                },
            ])
            ->add('adress',TextType::class,[
                'help'=> 'numero et libéllé de voie',
                'attr'=>['placeholder'=>'Saisissez l\'adresse']
            ])
            ->add('coordonnees')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Localisation::class,
        ]);
    }
}
