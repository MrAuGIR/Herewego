<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Transport;
use App\Entity\Localisation;
use App\Form\LocalisationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;




class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $now = (new \DateTime('now'))->format('Y-m-d');
        $builder
            //Localisation de départ
            ->add('localisation_start',LocalisationType::class)
            ->add('goStartedAt',DateTimeType::class, [
                'label'=> 'Quand ?',
                'widget' => 'single_text',
                'html5'=> true,
                'attr'=> ['placeholder'=> 'Date de départ',
                            'min'=> $now.'T00:00'] //$now->format('Y-m-dTH:i')]
            ])
            ->add('goEndedAt',DateTimeType::class, [
                'label'=> 'Arrivé ? (heure approximative)',
                'widget' => 'single_text',
                'html5' => true,
                'attr'=>['placeholder'=> 'Date et heure d\'arrivé',
                         'min'=> $now.'T00:00']
            ])
            //Localisation de retour
            ->add('localisation_return', LocalisationType::class)
            ->add('returnStartedAt', DateTimeType::class, [
                'label' => 'Quand ?',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['placeholder' => 'Date de retour',
                            'min' => $now .'T00:00']
            ])
            ->add('returnEndedAt', DateTimeType::class, [
                'label' => 'Arrivé ? (heure approximative)',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['placeholder' => 'Date et heure d\'arrivé au retour',
                            'min' => $now . 'T00:00']
            ])
            ->add('placePrice', MoneyType::class, [
                'label' => 'Prix par place',
                'attr' => ['placeholder'=> 'Prix/place']
            ])
            ->add('totalPlace', IntegerType::class, [
                'label' => 'Nombre de place',
                'attr' => ['placeholder'=> 'nombre de place']
            ])
            ->add('commentary', TextAreaType::class, [
                'label' => 'Informations complémentaires',
                'attr'=>['placeholder'=> 'Indiquer des informations supplémentaires sur le transport']
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
