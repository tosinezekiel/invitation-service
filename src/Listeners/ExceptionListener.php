<?php 
namespace App\Listeners;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = new JsonResponse();
        $response->setContent(json_encode([
            'error' => $exception->getMessage(),
            'status' => $statusCode,
        ]));

        $response->setStatusCode($statusCode);

        $event->setResponse($response);
    }
}
