<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre Email',
                'attr' => ['placeholder' => 'renseigner votre email'],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Votre nom',
                'attr' => ['placeholder' => 'renseigner votre nom'],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'votre prénom',
                'attr' => ['placeholder' => 'veuillez renseigner votre prénom'],
            ])
            ->add('phone', TextType::class, [
                'label' => 'votre numéro de téléphone',
                'attr' => ['placeholder' => 'renseigner votre numéro de téléphone'],
            ])
            ->add('localisation', LocalisationType::class, [
                'label' => false, // fait disparaitre le 'localisation' dans le formulaire
            ]);

        if (in_array('ROLE_ORGANIZER', $options['chosen_role'])) {
            $builder->add('companyName', TextType::class, [
                'label' => 'votre raison social',
                'attr' => ['placeholder' => 'renseigner votre raison social'],
                'required' => false,

            ])
                ->add('siret', TextType::class, [
                    'label' => 'votre numéro de siret',
                    'attr' => ['placeholder' => 'renseigner le numéro de siret de votre société'],
                    'required' => false,
                ])
                ->add('webSite', TextType::class, [
                    'label' => 'Site web',
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'chosen_role' => ['ROLE_USER'],
        ]);
    }
}
