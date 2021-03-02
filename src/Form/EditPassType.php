<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EditPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newPassword', TextType::class, [
                'label' => "Votre nouveau mot de passe",
                'attr' => [
                    'placeholder' => "Tapez votre nouveau mot de passe"
                ],
                'required' => false
            ])
            ->add('newPasswordRepeat', TextType::class, [
                'label' => "Ressaisissez votre nouveau mot de passe",
                'attr' => [
                    'placeholder' => "Ressaisissez votre nouveau mot de passe"
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
