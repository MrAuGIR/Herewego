<?php

namespace App\Form;

use App\Entity\QuestionUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => "Sujet de la question",
                'attr' => [
                    'placeholder' => "Entrez le sujet de votre question en quelques mots"
                ],
                'required' => false
            ])
            ->add('question', TextareaType::class, [
                'label' => "Votre Question",
                'attr' => [
                    'placeholder' => "Entrez votre question (255 caractères maximum)"
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuestionUser::class,
        ]);
    }
}
