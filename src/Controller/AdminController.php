<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Form\CreateUserType;
use App\Form\IncommingTransactionsType;
use App\Form\OutcommingTransactionsType;
use App\Form\NewArticleType;
use App\Form\NewStorageType;
use App\Repository\ArticleRepository;
use App\Repository\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(ArticleRepository $articleRepository, WarehouseRepository $warehouseRepo): Response
    {
        $articles = $articleRepository->findAll();
        $warehouse = $warehouseRepo->findAll();
        return $this->render('admin/index.html.twig', ["articles" => $articles, "warehouses" => $warehouse]);
    }

    #[Route('/dashboard/createNewUser', name: 'app_admin_new_user')]
    public function createUser(): Response
    {
        $new_user = new User();
        $form = $this->createForm(CreateUserType::class, $new_user);
        return $this->render('admin/create-user.html.twig', ["form" => $form]);
    }

    #[Route('/article/inbound', name: 'app_admin_inbound')]
    public function inbound(): Response
    {
        $incommingTransaction = new Transaction();
        $form = $this->createForm(IncommingTransactionsType::class, $incommingTransaction);
        return $this->render('inbound.html.twig', ["form" => $form]);
    }

    #[Route('/article/outbound', name: 'app_admin_outbound')]
    public function outbound(): Response
    {
        $outcommingTransaction = new Transaction();
        $form = $this->createForm(OutcommingTransactionsType::class, $outcommingTransaction);
        return $this->render('outbound.html.twig', ["form" => $form]);
    }

    #[Route('/storage/create-storage', name: 'app_admin_create_storage')]
    public function createStorage(): Response
    {
        $warehouse = new Warehouse();
        $form = $this->createForm(NewStorageType::class, $warehouse);
        return $this->render('admin/create-storage.html.twig', ["form" => $form]);
    }

    #[Route('/article/create-article', name: 'app_admin_create_article')]
    public function createArticle(): Response
    {
        $article = new Article();
        $form = $this->createForm(NewArticleType::class, $article);
        return $this->render('admin/create-article.html.twig', ["form" => $form]);
    }
}
