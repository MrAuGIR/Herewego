<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newPassword', PasswordType::class, [
                'label' => 'Votre nouveau mot de passe',
                'attr' => [
                    'placeholder' => 'Votre nouveau mot de passe',
                ],
                'required' => false,
            ])
            ->add('newPasswordRepeat', PasswordType::class, [
                'label' => 'Confirmez votre mot de passe',
                'attr' => [
                    'placeholder' => 'Confirmez votre mot de passe',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
