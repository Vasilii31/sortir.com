<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;


class ExceptionListener implements EventSubscriberInterface
{
    private RouterInterface $router;
    private LoggerInterface $logger;

    public function __construct(RouterInterface $router, LoggerInterface $logger)
    {
        $this->router = $router;
        $this->logger = $logger;

    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Log l'exception (facultatif mais recommandé)
        $this->logger->error('Exception interceptée : '.$exception->getMessage(), [
            'exception' => $exception,
        ]);

        $data = [
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'class'   => get_class($exception),
        ];

        // Exemple : redirection vers une page d'erreur custom
        $response = new RedirectResponse($this->router->generate('error_page'));
//        $response = new Response(
//            $this->twig->render('error/error.html.twig', $data),
//            Response::HTTP_INTERNAL_SERVER_ERROR
//        );
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}