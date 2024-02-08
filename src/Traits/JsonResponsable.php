<?php 
namespace App\Traits;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;

trait JsonResponsable
{
    public function jsonSuccessResponse(string $message, array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'data' => $data,
            'message' => $message
        ], $status);
    }

    public function jsonErrorResponse(string $message, array $data = [], int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse([
            'error' => "Error.",
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function jsonFormErrorResponse($violations, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $this->jsonErrorResponse("Unprocessable Entity", $errors, JsonResponse::HTTP_BAD_REQUEST);
    }
}
