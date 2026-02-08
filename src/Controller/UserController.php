<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\IncommingTransactionsType;
use App\Form\OutcommingTransactionsType;
use App\Repository\ArticleRepository;
use App\Repository\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'app_user_dashboard')]
    public function index(ArticleRepository $articleRepository, WarehouseRepository $warehouseRepo): Response
    {
        $user = $this->getUser();
        $userWarehouses = $warehouseRepo->findBy(['users' => $user]);
        $articles = $articleRepository->findAll();

        return $this->render('user/index.html.twig', [
            "articles" => $articles,
            "warehouses" => $userWarehouses
        ]);
    }

    #[Route('/article/inbound', name: 'app_user_inbound')]
    public function inbound(): Response
    {
        $incommingTransaction = new Transaction();
        $form = $this->createForm(IncommingTransactionsType::class, $incommingTransaction);
        return $this->render('inbound.html.twig', ["form" => $form]);
    }

    #[Route('/article/outbound', name: 'app_user_outbound')]
    public function outbound(): Response
    {
        $outcommingTransaction = new Transaction();
        $form = $this->createForm(OutcommingTransactionsType::class, $outcommingTransaction);
        return $this->render('outbound.html.twig', ["form" => $form]);
    }
}
