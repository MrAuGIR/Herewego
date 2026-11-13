<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Picture;
use App\Service\Files\PictureService;
use Symfony\Component\Form\FormInterface;

readonly class PictureFactory
{
    public function __construct(
        private PictureService $pictureService,
    ) {
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return \Generator<int, Picture>
     */
    public function handleFromForm(FormInterface $form): \Generator
    {
        $pictures = $form->get('pictures')->getData();

        foreach ($pictures as $picture) {
            yield $this->pictureService->handleCreate($picture);
        }
    }
}
