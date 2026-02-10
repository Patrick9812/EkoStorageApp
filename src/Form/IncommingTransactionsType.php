<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Transaction;
use App\Entity\Warehouse;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class IncommingTransactionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'name',
                'label' => 'Artykuł',
                'placeholder' => 'Wybierz produkt...',
                'attr' => ['class' => 'form-select-lg']
            ])

            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'label' => 'Magazyn docelowy',
                'placeholder' => 'Wybierz magazyn...',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('w');
                    if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
                        return $qb->orderBy('w.name', 'ASC');
                    }

                    return $qb->innerJoin('w.users', 'u')
                        ->where('u.id = :user')
                        ->setParameter('user', $user)
                        ->orderBy('w.name', 'ASC');
                },
            ])

            ->add('quantity', NumberType::class, [
                'label' => 'Ilość przyjęta',
                'html5' => true,
                'attr' => ['step' => '0.001', 'placeholder' => '0.000']
            ])

            ->add('unit', TextType::class, [
                'label' => 'Jednostka miary',
                'disabled' => true,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Pobierana z artykułu',
                    'class' => 'bg-light'
                ],
                'help' => 'Jednostka zdefiniowana przez administratora dla wybranego artykułu.'
            ])

            ->add('priceNetto', MoneyType::class, [
                'label' => 'Cena jednostkowa Netto',
                'currency' => 'PLN',
                'help' => 'Cena zakupu za jednostkę',
            ])

            ->add('VAT', NumberType::class, [
                'label' => 'Stawka VAT (%)',
                'attr' => ['placeholder' => '23'],
            ])

            ->add('code', TextType::class, [
                'label' => 'Numer Partii / Dokumentu',
                'attr' => ['placeholder' => 'NP: FV/2024/01/01 lub EAN']
            ])

            ->add('documents', FileType::class, [
                'label' => 'Faktury / Dokumenty (PDF, XML)',
                'mapped' => false,
                'multiple' => true,
                'required' => true,
                'constraints' => [
                    new Count(
                        max: 4,
                        maxMessage: 'Możesz dodać maksymalnie 4 pliki'
                    ),
                    new All(
                        constraints: [
                            new File(
                                maxSize: '5M',
                                mimeTypes: [
                                    'application/pdf',
                                    'application/x-pdf',
                                    'text/xml',
                                    'application/xml',
                                ],
                                mimeTypesMessage: 'Proszę przesłać poprawny format dokumentu (PDF/XML)'
                            )
                        ]
                    )
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
            'validation_groups' => ['incoming', 'Default'],
        ]);

        $resolver->setRequired('user');
    }
}
