<?php

namespace App\Form;

use App\Entity\QuestionAdmin;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Validator\Constraints\Length;

class QuestionAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question',CKEditorType::class,[
                'config' => array('toolbar' => 'full'),
                'label' => 'Question :',
                'attr' => ['class'=> 'form-control']
            ])
            ->add('answer', CKEditorType::class, [
                'config' => array('toolbar' => 'full'),
                'label' => 'Réponse à donner :',
                'attr' => ['class' => 'form-control']
            ])
            ->add('importance',NumberType::class,[
                'attr' => [ 'min'=> 0,
                            'max' => 100,
                            'class' => 'form-control'
                 ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuestionAdmin::class,
        ]);
    }
}
