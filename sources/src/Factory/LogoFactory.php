<?php

namespace App\Factory;

use App\Entity\Category;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;

class LogoFactory
{
    public function __construct(
        #[Autowire(param: 'logo_directory')]
        private string $logoDirectory
    ) {
    }

    public function handleFromForm(FormInterface $form, Category $category): ?string
    {
        $logo = $form->get('pathLogo')->getData();

        if (empty($logo)) {
            return null;
        }

        $file = md5(uniqid()).'.'.$logo->guessExtension();

        $logo->move(
            $this->logoDirectory,
            $file
        );

        if (! empty($category->getPathLogo())) {
            unlink($this->logoDirectory.DIRECTORY_SEPARATOR.$category->getPathLogo());
        }

        return $file;
    }
}
