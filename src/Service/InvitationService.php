<?php 
namespace App\Service;

use App\Entity\User;
use App\Entity\Invitation;
use App\Utils\AppConstants;
use Symfony\Component\Mime\Email;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ExpiredResourceException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class InvitationService
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private InvitationRepository $invitationRepository, 
        private ParameterBagInterface $params,
        private MailerInterface $mailer
    ){}

    public function sendInvitation(string $email, User $sender): Invitation
    {
        $this->invalidatePreviousInvitations($email);
        
        $invitation = new Invitation();

        $expiryPeriod = $this->params->get('token_expiry_period');
        $expiresAt = new \DateTime(sprintf('+%d seconds', $expiryPeriod));
        $randomToken = bin2hex(random_bytes(4));

        $invitation->setEmail($email);
        $invitation->setSender($sender);
        $invitation->setToken($randomToken);
        $invitation->setUrl(AppConstants::BASE_URL . '?token=' . $randomToken);
        $invitation->setExpiresAt($expiresAt);

        $acceptUrl = AppConstants::BASE_URL . '/api/invites/accept?token=' . $randomToken;
        $declineUrl = AppConstants::BASE_URL . '/api/invites/decline?token=' . $randomToken;

        $htmlContent = sprintf(
            '<p>You have been invited by %s.<br> Click <a href="%s">here</a> to accept the invitation.</p> or <a href="%s">Decline</a>',
            htmlspecialchars($sender->getFirstName()),
            htmlspecialchars($acceptUrl),
            htmlspecialchars($declineUrl)
        );

        try {
            $emailMessage = (new Email())
                ->from($sender->getEmail()) 
                ->to($email)
                ->subject('You are Invited!')
                ->html($htmlContent);
 
            $this->mailer->send($emailMessage);

        }catch(\Exception $e){
            dd($e->getMessage());
        }
        
        $invitation->setStatus(AppConstants::SENT_INVITE);

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        return $invitation;
    }

    public function handleInvitation(string $token, bool $accept): Invitation 
    {
        $invitation = $this->invitationRepository->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw new NotFoundHttpException('Invitation not found.');
        }

        if (!$invitation->getIsActive() || new \DateTime() > $invitation->getExpiresAt()) {
            throw new ExpiredResourceException('Invitation not found.');
        }

        $accept ? $invitation->setStatus(AppConstants::ACCEPT_INVITE) : $invitation->setStatus(AppConstants::DECLINE_INVITE);

        $this->entityManager->flush();

        return $invitation;
    }

    public function cancelInvitation(int $id, $user): void 
    {
        $invitation = $this->invitationRepository->find($id);

        if (!$invitation) {
            throw new NotFoundHttpException('This invitation could not be found.');
        }

        if ($invitation->getSender() !== $user) {
            throw new AccessDeniedException('You do not have permission to cancel this invitation.');
        }

        $invitation->cancel();

        $this->entityManager->flush();
    }

    private function invalidatePreviousInvitations(string $email): void
    {
        $invitations = $this->entityManager->getRepository(Invitation::class)->findBy([
            'email' => $email,
            'status' => AppConstants::SENT_INVITE 
        ]);

        foreach ($invitations as $invitation) {
            $invitation->cancel();
        }

        $this->entityManager->flush();
    }

}

