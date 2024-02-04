<?php

namespace App\Controller\API;

use App\Traits\JsonResponsable;
use App\DTO\InvitationRequestDTO;
use App\Service\InvitationService;
use App\Exception\ExpiredResourceException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvitationController extends AbstractController
{
    use JsonResponsable;

    public function __construct(
        private InvitationService $invitationService, 
        private SerializerInterface $serializer, 
        private ValidatorInterface $validator
    )
    {}

    #[Route('/api/auth/invites', methods: ['POST'])]
    public function send(Request $request): Response
    {
        $user = $this->getUser();

        $dto = $this->serializer->deserialize($request->getContent(), InvitationRequestDTO::class, 'json');

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return $this->jsonFormErrorResponse($violations);
        }

        $invitation = $this->invitationService->sendInvitation($dto->email, $user);

        return $this->jsonSuccessResponse('Invitation sent successfully', [
            'invitation' => $invitation->getUrl()
        ]);
    }

    #[Route('/api/auth/invites/{id}/cancel', methods: ['GET'])]
    public function cancel(int $id): Response
    {
        $user = $this->getUser();
        $this->invitationService->cancelInvitation($id, $user);
        return $this->jsonSuccessResponse('Invitation canceled successful.', []);
    }

    #[Route('/public/invites/{token}/accept', methods: ['PATCH'])]
    public function accept(string $token): Response
    {
        try {
            $invitation = $this->invitationService->handleInvitation($token, true);

            return $this->jsonSuccessResponse('Invitation accepted successfully.');

        } catch (ExpiredResourceException $e) {
            return $this->jsonErrorResponse($e->getMessage(), [], $e->getStatusCode());
        } catch (NotFoundHttpException $e) {
            return $this->jsonErrorResponse($e->getMessage(), [], $e->getStatusCode());
        }
        
    }

    #[Route('/public/invites/{token}/decline', methods: ['PATCH'])]
    public function decline(string $token): Response
    {
        try {
            $invitation = $this->invitationService->handleInvitation($token, false);

            return $this->jsonSuccessResponse('Invitation accepted successfully.');

        } catch (ExpiredResourceException $e) {
            return $this->jsonErrorResponse($e->getMessage(), [], $e->getStatusCode());
        } catch (NotFoundHttpException $e) {
            return $this->jsonErrorResponse($e->getMessage(), [], $e->getStatusCode());
        }
    }

}
