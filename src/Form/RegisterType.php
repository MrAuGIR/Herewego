<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'chosen_role' => ['ROLE_USER']
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('email',EmailType::class,[
                'label'=> 'Votre Email',
                'attr'=> ['placeholder'=>'renseigner votre email']
            ])
            ->add('password',PasswordType::class,[
                'label' => 'Votre mot de passe',
                'attr'=>['placeholder'=>'renseigner votre mot de passe'] 
            ])
            ->add('confirmPassword',PasswordType::class,[
                'label' => 'Confirmer le mot de passe',
                'attr'=>['placeholder'=>'confimer le mot de passe']
            ])
            ->add('lastname', TextType::class,[
                'label'=> 'Votre nom',
                'attr'=> ['placeholder'=>'renseigner votre nom']
            ])
            ->add('firstname', TextType::class,[
                'label'=> 'votre prénom',
                'attr'=>['placeholder'=>'veuillez renseigner votre prénom']
            ])
            ->add('phone', TextType::class, [
                'label' => 'votre numéro de téléphone',
                'attr' => ['placeholder' => 'renseigner votre numéro de téléphone']
            ])
            ->add('localisation', LocalisationType::class);
            

        if(in_array('ROLE_ORGANIZER', $options['chosen_role'])){
            $builder->add('companyName', TextType::class, [
                'label' => 'votre raison social',
                'attr' => ['placeholder' => 'renseigner votre raison social'],
                'required' => false,
                'mapped' => true

            ])
            ->add('siret', TextType::class, [
                'label' => 'votre numéro de siret',
                'attr' => ['placeholder' => 'renseigner le numéro de siret de votre société'],
                'required' => false,
                'mapped' => true
            ])
            ->add('webSite', TextType::class, [
                'label' => 'Site web',
                'required'=>false,
                'mapped'=> true
            ]);
        }
            
                       
    }

}
