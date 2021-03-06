<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\EventGroup;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => "Titre de l'évênement",
                'attr' => [
                    'placeholder' => "Le titre de l'évênement"
                ],
                'required' => false
            ])
            ->add('description', CKEditorType::class, [
                'label' => "Description de l'évênement",
                'attr' => [
                    'placeholder' => "Une description de l'évênement"
                ],
                'required' => false
            ])
            ->add('startedAt', DateTimeType::class, [
                'label' => "Début de l'évênement",
                'date_widget'=> 'single_text',
                'time_widget'=> 'single_text',
                'required' => false
            ])
            ->add('endedAt', DateTimeType::class, [
                'label' => "Fin de l'évênement",
                'date_widget'=> 'single_text',
                'time_widget'=> 'single_text',
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => "Email lié à l'évênement",
                'attr' => [
                    'placeholder' => "Email lié à l'évênement"
                ],
                'required' => false
            ])
            ->add('website', UrlType::class, [
                'label' => "Site web de l'évênement",
                'attr' => [
                    'placeholder' => "L'url du site web de l'évênement"
                ],
                'required' => false
            ])
            ->add('phone', TelType::class, [
                'label' => "Numéro de téléphone lié à l'évênement",
                'attr' => [
                    'placeholder' => "Le numéro de téléphone lié à l'évênement"
                ],
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'placeholder' => '-- Choisir une catégorie --',
                'class' => Category::class,
                'choice_label' => function (Category $category) {
                    return strtoupper($category->getName());
                },
                'required' => false
            ])
            ->add('eventGroup', EntityType::class, [
                'label' => "Groupe d'évênements",
                'placeholder' => "-- Choisir une groupe d'évênements --",
                'class' => EventGroup::class,
                'choice_label' => function (EventGroup $eventGroup) {
                    return strtoupper($eventGroup->getName());
                },
                'required' => false
            ])
            ->add('facebookLink', UrlType::class, [
                'label' => "Lien Facebook",
                'attr' => [
                    'placeholder' => "Tapez un lien facebook"
                ],
                'required' => false
            ])
            ->add('instagramLink', UrlType::class, [
                'label' => "Lien Instagram",
                'attr' => [
                    'placeholder' => "Tapez un lien instagram"
                ],
                'required' => false
            ])
            ->add('twitterLink', UrlType::class, [
                'label' => "Lien Twitter",
                'attr' => [
                    'placeholder' => "Tapez un lien Twitter"
                ],
                'required' => false
            ])
            ->add('localisation', LocalisationType::class,[
                'label'=> false,
                'required' => false
            ])
            ->add('pictures', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false
            ]);
            
            // ->add('picturePath', TextType::class, [
            //     'label' => "Chemin de l'image de l'event",
            //     'attr' => [
            //         'placeholder' => "Chemin de l'image de l'event"
            //     ],
            //     'required' => false,
            //     'mapped' => false
            // ])
            // ->add('pictureTitle', TextType::class, [
            //     'label' => "Titre de l'image de l'event",
            //     'attr' => [
            //         'placeholder' => "Titre de l'image de l'event"
            //     ],
            //     'required' => false,
            //     'mapped' => false
            // ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
