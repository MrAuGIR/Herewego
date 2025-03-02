<?php


namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',TextType::class,[
            'label' => 'Nom',
            'attr' => ['placeholder'=>'nom de la category'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom de la catégorie est obligatoire',
                ]),
            ]
        ])
        ->add('color',ColorType::class,[
            'label'=>'Couleur',
        ])
        ->add('pathLogo',FileType::class,[
            'label' => 'Logo',
            'mapped'=> false,
            'required' => false,
        ])
        ;
    }
}