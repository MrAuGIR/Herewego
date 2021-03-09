<?php


namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints\NotBlank;
=======
>>>>>>> 7c864d5 (crud category (manque le edit))
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class,[
<<<<<<< HEAD
            'label' => 'Nom',
            'attr' => ['placeholder'=>'nom de la category'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom de la catégorie est obligatoire',
                ]),
            ]
=======
            'label'=>'Nom',
            'attr'=>['placeholder'=>'nom de la category']
>>>>>>> 7c864d5 (crud category (manque le edit))
        ])
        ->add('color',ColorType::class,[
            'label'=>'Couleur',
        ])
        ->add('pathLogo',FileType::class,[
<<<<<<< HEAD
            'label' => 'Logo',
            'mapped'=> false,
            'required' => false,
=======
            'label'=>'Logo',
>>>>>>> 7c864d5 (crud category (manque le edit))
        ])
        ;
    }
}