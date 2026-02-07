<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
// #[IsGranted('ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', []);
    }

    #[Route('/dashboard/createNewUser', name: 'app_admin_new_user')]
    public function createUser(): Response
    {
        return $this->render('admin/index.html.twig', []);
    }

    #[Route('/article/inbound', name: 'app_admin_inbound')]
    public function inbound(): Response
    {
        return $this->render('admin/inbound.html.twig', []);
    }

    #[Route('/article/outbound', name: 'app_admin_outbound')]
    public function outbound(): Response
    {
        return $this->render('admin/outbound.html.twig', []);
    }

    #[Route('/storage/create-storage', name: 'app_admin_create_storage')]
    public function createStorage(): Response
    {
        return $this->render('admin/create-storage.html.twig', []);
    }

    #[Route('/article/create-article', name: 'app_admin_create_article')]
    public function createArticle(): Response
    {
        return $this->render('admin/create-article.html.twig', []);
    }
}
