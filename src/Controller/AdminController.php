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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $newUser = new User();
        $form = $this->createForm(CreateUserType::class, $newUser);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $plainPassword = $form->get('password')->getData();

                $hashedPassword = $userPasswordHasher->hashPassword($newUser, $plainPassword);
                $newUser->setPassword($hashedPassword);

                $entityManager->persist($newUser);
                $entityManager->flush();

                $this->addFlash('success', 'Użytkownik utworzony!');
                return $this->redirectToRoute('app_admin_dashboard');
            } else {
            }
        }

        return $this->render('admin/create-user.html.twig', [
            "form" => $form
        ]);
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
    public function createStorage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $warehouse = new Warehouse();
        $form = $this->createForm(NewStorageType::class, $warehouse);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($warehouse);
            $entityManager->flush();

            $this->addFlash('success', 'Magazyn ' . $warehouse->getName() . ' został utworzony!');

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/create-storage.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/article/create-article', name: 'app_admin_create_article', methods: ['GET', 'POST'])]
    public function createArticle(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(NewArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', 'Artykuł ' . $article->getName() . ' został dodany.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/create-article.html.twig', [
            "form" => $form
        ]);
    }
}
