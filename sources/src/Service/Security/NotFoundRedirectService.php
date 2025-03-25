<?php

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class NotFoundRedirectService
{
    private const MAPPING_REDIRECTION = [
        '/event/category/' => [
            'route' => 'home',
            'message' => 'Category not found',
        ],
        '/event/group/' => [
            'route' => 'home',
            'message' => 'Group not found',
        ]
    ];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack
    ) {}

    public function handle(Request $request): ?RedirectResponse
    {
        if (!empty($mapping = $this->findMapping($request->getPathInfo()))) {

            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('warning', $mapping['message']);

            return new RedirectResponse($this->router->generate($mapping['route']));
        }
        return null;
    }

    private function findMapping(string $route): ?array
    {
        foreach (self::MAPPING_REDIRECTION as $uri => $mapping) {
            if (str_starts_with($route, $uri)) {
                return $mapping;
            }
        }
        return null;
    }
}