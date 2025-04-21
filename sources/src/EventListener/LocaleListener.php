<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest', priority: 10)]
readonly class LocaleListener
{
    public function onKernelRequest(RequestEvent $event):void
    {
        $request = $event->getRequest();

        if ($locale = $request->getSession()->get('_locale')) {
            $request->setLocale($locale);
        }
    }
}