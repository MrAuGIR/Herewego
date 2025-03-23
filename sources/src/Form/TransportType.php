<?php

namespace App\Form;

use App\Entity\Localisation;
use App\Entity\Transport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $now = (new \DateTime('now'))->format('Y-m-d');
        $builder
            // Localisation de départ
            ->add('localisation_start', LocalisationType::class)
            ->add('goStartedAt', DateTimeType::class, [
                'label' => 'Quand ?',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Date de départ',
                    'min' => $now.'T00:00',
                ],

            ])
            ->add('goEndedAt', DateTimeType::class, [
                'label' => 'Arrivé ? (heure approximative)',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Date et heure d\'arrivé',
                    'min' => $now.'T00:00',
                ],
            ])
            // Localisation de retour
            ->add('localisation_return', LocalisationType::class)
            ->add('returnStartedAt', DateTimeType::class, [
                'label' => 'Quand ?',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Date de retour',
                    'min' => $now.'T00:00',
                ],
            ])
            ->add('returnEndedAt', DateTimeType::class, [
                'label' => 'Arrivé ? (heure approximative)',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'placeholder' => 'Date et heure d\'arrivé au retour',
                    'min' => $now.'T00:00',
                ],
            ])
            ->add('placePrice', MoneyType::class, [
                'label' => 'Prix par place',
                'required' => false,
                'attr' => ['placeholder' => 'Prix/place'],
            ])
            ->add('totalPlace', IntegerType::class, [
                'label' => 'Nombre de place',
                'required' => false,
                'attr' => ['placeholder' => 'nombre de place'],
            ])
            ->add('commentary', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'required' => false,
                'attr' => ['placeholder' => 'Indiquer des informations supplémentaires sur le transport'],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary',
                    'value' => 'Créé'],
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transport::class,
        ]);
    }
}
