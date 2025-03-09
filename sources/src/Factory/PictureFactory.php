<?php

namespace App\Factory;

use App\Entity\Picture;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;

class PictureFactory
{
    public function __construct(
        #[Autowire(param: 'images_directory')]
        private string $imagesDirectory
    ) {
    }

    public function handleFromForm(FormInterface $form): iterable
    {
        $pictures = $form->get('pictures')->getData();

        foreach ($pictures as $picture) {
            $file = md5(uniqid()).'.'.$picture->guessExtension();

            $picture->move(
                $this->imagesDirectory,
                $file
            );

            $img = new Picture();
            $img->setPath($file)
                ->setOrderPriority(1);

            yield $img;
        }
    }
}
