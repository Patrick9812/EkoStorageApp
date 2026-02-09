<?php

namespace App\Controller;

use App\Entity\InvoiceFile;
use App\Entity\Transaction;
use App\Entity\User;
use App\Enum\TransactionType;
use App\Form\IncommingTransactionsType;
use App\Form\OutcommingTransactionsType;
use App\Repository\ArticleRepository;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'app_user_dashboard')]
    public function index(ArticleRepository $articleRepository, WarehouseRepository $warehouseRepo): Response
    {
        $userWarehouses = $warehouseRepo->findAllForUser($this->getUser());

        $articles = $articleRepository->findAll();

        return $this->render('user/index.html.twig', [
            "articles" => $articles,
            "warehouses" => $userWarehouses
        ]);
    }

    #[Route('/article/inbound', name: 'app_user_inbound', methods: ['GET', 'POST'])]
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
                        return $this->redirectToRoute('app_user_inbound');
                    }
                }
            }

            $transaction->setType(TransactionType::IN);
            $transaction->setUser($user);

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Pomyślnie przyjęto towar na magazyn.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('inbound.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/article/outbound', name: 'app_user_outbound')]
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
                        'form' => $form,
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
                return $this->redirectToRoute('app_user_dashboard');
            } else {
                foreach ($form->getErrors(true) as $error) {
                    if (
                        $error->getCause() instanceof \Symfony\Component\Security\Csrf\Exception\TokenNotFoundException ||
                        str_contains($error->getMessage(), 'CSRF')
                    ) {
                        $this->addFlash('error', 'Twoja sesja wygasła. Formularz został odświeżony, spróbuj ponownie.');
                        return $this->redirectToRoute('app_user_outbound');
                    }
                    $this->addFlash('error', 'Błąd: ' . $error->getMessage());
                }
            }
        }

        return $this->render('outbound.html.twig', [
            'form' => $form
        ]);
    }
}
