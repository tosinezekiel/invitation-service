<?php 
namespace App\Entity;

use DateTimeInterface;
use App\Traits\Timestamps;
use App\Utils\AppConstants;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Invitation
{
    use Timestamps; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "invitations", cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $sender;

    #[ORM\Column(type: Types::STRING)]
    private string $email; 

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $token;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $url;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $expiresAt; 

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private string $status;
    
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true; 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setIsActive(string $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getIsActive(): string
    {
        return $this->isActive;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function cancel(): self
    {
        $this->setStatus(AppConstants::CANCEL_INVITE);
        $this->setIsActive(false);
        $this->setToken(null);
        $this->setUrl(null);
        $this->setExpiresAt(null);

        return $this;
    }
}
