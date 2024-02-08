<?php

namespace App\Controller\API;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AuthController extends AbstractController {
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login()
    {
       
    }
}