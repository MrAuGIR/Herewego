<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\EventListener\EntityNotFoundListener;
use App\EventListener\LocaleListener;
use App\Security\AccessDeniedHandler;
use App\Security\UserChecker;
use App\Service\Security\NotFoundRedirectService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class SecurityComponentsTest extends TestCase
{
    private static function requestWithSession(string $path = '/'): Request
    {
        $request = Request::create($path);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    private static function flashes(Request $request, string $type): mixed
    {
        $session = $request->getSession();
        self::assertInstanceOf(Session::class, $session);

        return $session->getFlashBag()->get($type);
    }

    private function router(): RouterInterface
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturnCallback(static fn (string $route): string => '/'.$route);

        return $router;
    }

    public function testUserCheckerBlocksNonValidatedAccounts(): void
    {
        $checker = new UserChecker();

        $checker->checkPreAuth((new User())->setIsValidate(true));
        $checker->checkPreAuth(new InMemoryUser('other', null));
        $checker->checkPostAuth(new User());

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth((new User())->setIsValidate(false));
    }

    public function testAccessDeniedHandlerRedirectsHomeWithFlash(): void
    {
        $request = self::requestWithSession();

        $response = (new AccessDeniedHandler($this->router()))->handle($request, new AccessDeniedException('nope'));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/home', $response->getTargetUrl());
        self::assertNotEmpty(self::flashes($request, 'warning'));
    }

    public function testNotFoundOnKnownPrefixRedirectsWithFlash(): void
    {
        $request = self::requestWithSession('/event/category/999');
        $stack = new RequestStack();
        $stack->push($request);
        $service = new NotFoundRedirectService($this->router(), $stack);
        $listener = new EntityNotFoundListener($service);
        $kernel = $this->createStub(HttpKernelInterface::class);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new NotFoundHttpException());
        $listener->onKernelException($event);
        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertSame(['Category not found'], self::flashes($request, 'warning'));

        $unknown = new ExceptionEvent($kernel, Request::create('/nowhere'), HttpKernelInterface::MAIN_REQUEST, new NotFoundHttpException());
        $listener->onKernelException($unknown);
        self::assertNull($unknown->getResponse());

        $other = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new \RuntimeException());
        $listener->onKernelException($other);
        self::assertNull($other->getResponse());
    }

    public function testLocaleListenerAppliesSessionLocale(): void
    {
        $request = self::requestWithSession();
        $request->getSession()->set('_locale', 'en');

        (new LocaleListener())->onKernelRequest(new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('en', $request->getLocale());
    }
}
