<?php 
use Faker\Factory;
use App\Entity\User;
use App\Entity\Invitation;
use App\Utils\AppConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvitationControllerTest extends WebTestCase
{
  
    public function testSendInvitation(): void
    {
        $client = static::createClient();
        $client->logInUser($this->createUser()); 

        $data = [
            'email' => 'recipient@example.com',
        ];
        $client->request('POST', '/api/auth/invites', [], [], [], json_encode($data));
        
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testCancelInvitation(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->logInUser($user);

        $invitation = new Invitation();
        $invitation->setCreatedAt(new \DateTime('+1 hour'));
        $invitation->setToken('test_token');
        $invitation->setStatus(AppConstants::SENT_INVITE);
        $invitation->setEmail("ex@test.com");
        $invitation->setSender($user);

        $savedInvitation = $this->saveInvitation($invitation);

        $client->request('PATCH',  sprintf('/api/auth/invites/%s/cancel', $savedInvitation->getId()));
        $response = $client->getResponse();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $updatedInvitation = $entityManager->getRepository(Invitation::class)->findOneBy(['token' => $savedInvitation->getToken()]);

        $this->assertSame($updatedInvitation->getStatus(), AppConstants::CANCEL_INVITE);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
    }

    public function testAcceptInvitation(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $date = new \DateTime('+1 hour');
        $invitation = new Invitation();
        $invitation->setExpiresAt($date);
        $invitation->setToken('xxxtoken');
        $invitation->setStatus(AppConstants::SENT_INVITE);
        $invitation->setEmail("ex@test.com");
        $invitation->setIsActive(true);
        $invitation->setSender($user);

        $savedInvitation = $this->saveInvitation($invitation);
       
        $client->request('PATCH',  sprintf('/public/invites/%s/accept', $savedInvitation->getToken()));
        $response = $client->getResponse();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $updatedInvitation = $entityManager->getRepository(Invitation::class)->findOneBy(['token' => $savedInvitation->getToken()]);

        $this->assertSame($updatedInvitation->getStatus(), AppConstants::ACCEPT_INVITE);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
    }

    public function testDeclineInvitation(): void
    {
        $client = static::createClient();
        $user = $this->createUser();

        $invitation = new Invitation();
        $invitation->setExpiresAt(new \DateTime('+1 hour'));
        $invitation->setToken('xxxtoken');
        $invitation->setStatus(AppConstants::SENT_INVITE);
        $invitation->setEmail("ex@test.com");
        $invitation->setIsActive(true);
        $invitation->setSender($user);

        $savedInvitation = $this->saveInvitation($invitation);

        $client->request('PATCH',  sprintf('/public/invites/%s/decline', $savedInvitation->getToken()));
        $response = $client->getResponse();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $updatedInvitation = $entityManager->getRepository(Invitation::class)->findOneBy(['token' => $savedInvitation->getToken()]);

        $this->assertSame($updatedInvitation->getStatus(), AppConstants::DECLINE_INVITE);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
    }

    private function createUser(): User
    {
        $faker = Factory::create();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
    
        $user = new User();
        $user->setEmail($faker->email());
        $user->setFirstName($faker->firstName());
        $user->setLastName($faker->lastName());
        $user->setPassword($faker->password());

        $entityManager->persist($user);
        $entityManager->flush();
    
        return $user;
    }

    private function saveInvitation(Invitation $invitation): Invitation
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $entityManager->persist($invitation);
        $entityManager->flush();

        return $invitation;
    }
}
