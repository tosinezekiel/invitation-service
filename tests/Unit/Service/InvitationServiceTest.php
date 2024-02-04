<?php 
namespace App\Tests\Unit\Service;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Invitation;
use App\Utils\AppConstants;
use PHPUnit\Framework\TestCase;
use App\Service\InvitationService;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ExpiredResourceException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class InvitationServiceTest extends TestCase
{
    private $entityManagerMock;
    private $invitationRepositoryMock;
    private $parameterBagMock;
    private $mailerMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->invitationRepositoryMock = $this->createMock(InvitationRepository::class);
        $this->parameterBagMock = $this->createMock(ParameterBagInterface::class);
        $this->mailerMock = $this->createMock(MailerInterface::class);
    }


    public function testHandleInvitation(): void
    {
        $service = $this->service();

        $invitation = new Invitation();
        $invitation->setToken('test_token');
        $invitation->setExpiresAt(new \DateTime('+1 hour'));

        $this->invitationRepositoryMock->expects($this->any())
            ->method('findOneBy')
            ->with(['token' => 'test_token'])
            ->willReturn($invitation);

        $result = $service->handleInvitation('test_token', true);

        $this->assertInstanceOf(Invitation::class, $result);
        $this->assertEquals(AppConstants::ACCEPT_INVITE, $result->getStatus());

        $result = $service->handleInvitation('test_token', false);

        $this->assertInstanceOf(Invitation::class, $result);
        $this->assertEquals(AppConstants::DECLINE_INVITE, $result->getStatus());


        $invitation->setExpiresAt(new \DateTime('-1 hour'));
        $this->expectException(ExpiredResourceException::class);
        $service->handleInvitation('test_token', true);
    }

    public function testCancelInvitation(): void
    {
        $service = $this->service();

        $sender = $this->createUser();

        $anonymousUser = $this->createUser();

        $invitation = new Invitation();
        $invitation->setSender($sender);

        $this->invitationRepositoryMock->expects($this->any())
            ->method('find')
            ->with(1)
            ->willReturn($invitation);

        $service->cancelInvitation(1, $sender);

        $this->assertEquals(AppConstants::CANCEL_INVITE, $invitation->getStatus());

        $this->expectException(AccessDeniedException::class);
        $service->cancelInvitation(1, $anonymousUser); 
    }

    private function service()
    {
        return new InvitationService(
            $this->entityManagerMock,
            $this->invitationRepositoryMock,
            $this->parameterBagMock,
            $this->mailerMock
        );
    }

    private function createUser(): User
    {
        $faker = Factory::create();
    
        $user = new User();
        $user->setEmail($faker->email());
        $user->setFirstName($faker->firstName());
        $user->setLastName($faker->lastName());
        $user->setPassword($faker->password());
    
        return $user;
    }
    
}
