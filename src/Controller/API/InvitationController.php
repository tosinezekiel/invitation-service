<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    #[Route('/a/p/i/invitation', name: 'app_a_p_i_invitation')]
    public function index(): Response
    {
        return $this->render('api/invitation/index.html.twig', [
            'controller_name' => 'InvitationController',
        ]);
    }
}
