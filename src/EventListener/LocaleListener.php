<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RequestContextAwareInterface;

class LocaleListener implements EventSubscriberInterface
{
    private ?RequestContextAwareInterface $router;
    private string $defaultLocale;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, string $defaultLocale = 'en', ?RequestContextAwareInterface $router = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // Essayez de voir si la langue est définie dans la session
        if ($locale = $request->getSession()->get('_locale')) {
            $request->setLocale($locale);
        } else {
            // Sinon, utilisez la langue par défaut
            $request->setLocale($this->defaultLocale);
        }

        // Mettre à jour le contexte du routeur avec la langue actuelle
        if ($this->router !== null) {
            $this->router->getContext()->setParameter('_locale', $request->getLocale());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
