<?php

namespace App\EventListener;

use App\Service\Security\NotFoundRedirectService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsEventListener(event: 'kernel.exception', method: 'onKernelException', priority: 100)]
readonly class EntityNotFoundListener
{
    public function __construct(
        private NotFoundRedirectService $notFoundRedirectService
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (! $exception instanceof NotFoundHttpException) {
            return;
        }

        $response = $this->notFoundRedirectService->handle($event->getRequest());

        $event->setResponse($response);
    }
}
