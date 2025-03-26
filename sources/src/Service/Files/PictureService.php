<?php

namespace App\Service\Files;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

readonly class PictureService
{
    public function __construct(
        #[Autowire(param: 'images_directory')]
        private string $imagesDirectory,
        private EntityManagerInterface $em,
    ) {
    }

    public function handleCreate(File $picture): Picture
    {
        $file = md5(uniqid()).'.'.$picture->guessExtension();

        $picture->move(
            $this->imagesDirectory,
            $file
        );

        $img = new Picture();
        $img->setPath($file)
            ->setOrderPriority(1);

        return $img;
    }

    public function handleDelete(Picture $picture): void
    {
        unlink($this->getPictureFullPath($picture));

        $this->em->remove($picture);
        $this->em->flush();
    }

    private function getPictureFullPath(Picture $picture): string
    {
        return $this->imagesDirectory.'/'.$picture->getPath();
    }
}
