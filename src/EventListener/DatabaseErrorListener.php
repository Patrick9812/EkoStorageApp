<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;

class DatabaseErrorListener
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (
            $exception instanceof \Doctrine\DBAL\Exception\ConnectionException ||
            $exception instanceof \PDOException ||
            $exception instanceof \Doctrine\DBAL\Exception
        ) {
            $content = $this->twig->render('bundles/TwigBundle/Exception/db_error.html.twig');

            $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);
            $event->setResponse($response);
        }
    }
}
