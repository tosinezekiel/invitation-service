<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AuthRequestDTO
{
    #[Assert\NotBlank(message: "Email should not be blank.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    public string $email;

    #[Assert\NotBlank(message: "Password should not be blank.")]
    public string $password;
}
