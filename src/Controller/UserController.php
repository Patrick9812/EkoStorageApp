<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', []);
    }

    #[Route('/user/article/inbound', name: 'app_user_inbound')]
    public function inbound(): Response
    {
        return $this->render('user/inbound.html.twig', []);
    }

    #[Route('/user/article/outbound', name: 'app_user_outbound')]
    public function outbound(): Response
    {
        return $this->render('user/outbound.html.twig', []);
    }
}
