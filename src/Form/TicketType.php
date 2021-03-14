<?php

namespace App\Form;

use App\Entity\Ticket;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('askedAt')
            ->add('countPlaces',NumberType::class,[
                'constraints' => [
                    new NotNull(['message' => 'veuillez saisir une valeur'])
                ]
            ])
            ->add('commentary',CKEditorType::class,[
                'constraints' => [
                    new NotBlank(['message' => 'veuillez saisir un court message d\'indication'])
                ]
            ])
            // ->add('isValidate')
            // ->add('validateAt')
            // ->add('emailSendAt')
            // ->add('user')
            // ->add('transport')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
