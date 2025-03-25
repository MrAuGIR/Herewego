<?php

namespace App\Service\Files;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class PictureService
{
    public function __construct(
      #[Autowire(param: 'images_directory')] private string $images_directory,
        private EntityManagerInterface                      $em,
    ){}

    public function handleDelete(Picture $picture): void
    {
        unlink($this->getPictureFullPath($picture));

        $this->em->remove($picture);
        $this->em->flush();
    }

    private function getPictureFullPath(Picture $picture): string
    {
        return $this->images_directory.'/'.$picture->getPath();
    }
}