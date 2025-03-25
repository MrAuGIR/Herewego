<?php

namespace App\Factory;

use App\Service\Files\PictureService;
use Symfony\Component\Form\FormInterface;

readonly class PictureFactory
{
    public function __construct(
        private PictureService $pictureService,
    ) {
    }

    public function handleFromForm(FormInterface $form): iterable
    {
        $pictures = $form->get('pictures')->getData();

        foreach ($pictures as $picture) {

            yield $this->pictureService->handleCreate($picture);
        }
    }
}
