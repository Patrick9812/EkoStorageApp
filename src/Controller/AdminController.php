<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Enum\TransactionType;
use App\Entity\InvoiceFile;
use App\Form\CreateUserType;
use App\Form\IncommingTransactionsType;
use App\Form\OutcommingTransactionsType;
use App\Form\NewArticleType;
use App\Form\NewStorageType;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Repository\ArticleRepository;
use App\Repository\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


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

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $newUser->setPassword($userPasswordHasher->hashPassword($newUser, $plainPassword));

            $roles = $newUser->getRoles();

            if (!in_array('ROLE_USER', $roles)) {
                $roles[] = 'ROLE_USER';
            } else {
                $roles = [reset($roles)];
            }

            $newUser->setRoles($roles);

            $key = $_ENV['CRYPTO_KEY'] ?? $this->getParameter('kernel.secret');
            $cipher = 'aes-256-cbc';

            $plainFullname = $newUser->getFullname();

            if ($plainFullname) {
                $ivLength = openssl_cipher_iv_length($cipher);
                $iv = openssl_random_pseudo_bytes($ivLength);

                $encryptedFullname = openssl_encrypt($plainFullname, $cipher, $key, 0, $iv);
                $newUser->setFullname(base64_encode($iv) . ':' . $encryptedFullname);
            }

            $entityManager->persist($newUser);
            $entityManager->flush();

            $this->addFlash('success', 'Użytkownik utworzony z zaszyfrowanymi danymi!');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/create-user.html.twig', ["form" => $form]);
    }

    #[Route('/article/inbound', name: 'app_admin_inbound', methods: ['GET', 'POST'])]
    public function inbound(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[CurrentUser] ?User $user
    ): Response {
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $transaction = new Transaction();
        $form = $this->createForm(IncommingTransactionsType::class, $transaction, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $transaction->getArticle();
            if ($article) {
                $transaction->setUnit($article->getUnit());
            }

            $documentFiles = $form->get('documents')->getData();
            if ($documentFiles) {
                foreach ($documentFiles as $file) {
                    try {
                        $originalFilenameWithExt = $file->getClientOriginalName();
                        $originalFilename = pathinfo($originalFilenameWithExt, PATHINFO_FILENAME);

                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                        $file->move(
                            $this->getParameter('documents_directory'),
                            $newFilename
                        );

                        $invoiceFile = new InvoiceFile();
                        $invoiceFile->setFilename($newFilename);
                        $invoiceFile->setOriginalName($originalFilenameWithExt);

                        $transaction->addInvoiceFile($invoiceFile);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Błąd podczas wgrywania pliku: ' . $file->getClientOriginalName());
                        return $this->redirectToRoute('app_admin_inbound');
                    }
                }
            }

            $transaction->setType(TransactionType::IN);
            $transaction->setUser($user);

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Pomyślnie przyjęto towar na magazyn.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('inbound.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/article/outbound', name: 'app_admin_outbound', methods: ['GET', 'POST'])]
    public function outbound(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?User $user
    ): Response {
        if (!$user) {
            $this->addFlash('error', 'Musisz być zalogowany, aby wykonać tę operację.');
            return $this->redirectToRoute('app_login');
        }

        $transaction = new Transaction();
        $form = $this->createForm(OutcommingTransactionsType::class, $transaction, [
            'user' => $user,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $article = $transaction->getArticle();
                $warehouse = $transaction->getWarehouse();
                $requestedQuantity = (float) $transaction->getQuantity();

                $allTransactions = $entityManager->getRepository(Transaction::class)->findBy([
                    'article' => $article,
                    'warehouse' => $warehouse
                ]);

                $currentStock = 0;
                foreach ($allTransactions as $t) {
                    $qty = (float) $t->getQuantity();
                    if ($t->getType() === TransactionType::IN) {
                        $currentStock += $qty;
                    } else {
                        $currentStock -= $qty;
                    }
                }

                if ($requestedQuantity > $currentStock) {
                    $this->addFlash('error', sprintf(
                        'Błąd: Na magazynie %s jest tylko %.3f %s. Próbujesz wydać %.3f.',
                        $warehouse->getName(),
                        $currentStock,
                        $article ? $article->getUnit() : 'szt',
                        $requestedQuantity
                    ));

                    return $this->render('outbound.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                $transaction->setType(TransactionType::OUT);
                $transaction->setUser($user);
                if ($article) {
                    $transaction->setUnit($article->getUnit());
                }

                $entityManager->persist($transaction);
                $entityManager->flush();

                $this->addFlash('success', 'Pomyślnie wydano towar z magazynu.');
                return $this->redirectToRoute('app_admin_dashboard');
            } else {
                foreach ($form->getErrors(true) as $error) {
                    if (
                        $error->getCause() instanceof \Symfony\Component\Security\Csrf\Exception\TokenNotFoundException ||
                        str_contains($error->getMessage(), 'CSRF')
                    ) {
                        $this->addFlash('error', 'Twoja sesja wygasła. Formularz został odświeżony, spróbuj ponownie.');
                        return $this->redirectToRoute('app_admin_outbound');
                    }
                    $this->addFlash('error', 'Błąd: ' . $error->getMessage());
                }
            }
        }

        return $this->render('outbound.html.twig', [
            'form' => $form
        ]);
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
