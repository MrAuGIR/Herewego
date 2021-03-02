<?php

namespace App\Form;

use App\Entity\Transport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goStartedAt')
            ->add('goEndedAt')
            ->add('returnStartedAt')
            ->add('returnEndedAt')
            ->add('placePrice')
            ->add('totalPlace')
            ->add('remainingPlace')
            ->add('commentary')
            ->add('createdAt')
            ->add('event')
            ->add('localisation_start')
            ->add('localisation_return')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transport::class,
        ]);
    }
}
