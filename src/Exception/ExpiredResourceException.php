<?php 

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExpiredResourceException extends HttpException
{
    public function __construct(string $message = 'Resource has expired.', int $statusCode = Response::HTTP_GONE, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
