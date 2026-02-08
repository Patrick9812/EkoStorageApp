<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class IncommingTransactionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'name',
                'label' => 'Artykuł',
                'placeholder' => 'Wybierz produkt...',
                'attr' => ['class' => 'form-select-lg']
            ])

            ->add('quantity', NumberType::class, [
                'label' => 'Ilość przyjęta',
                'html5' => true,
                'attr' => ['step' => '0.001', 'placeholder' => '0.000']
            ])

            ->add('priceNetto', MoneyType::class, [
                'label' => 'Cena jednostkowa Netto',
                'currency' => 'PLN',
                'help' => 'Cena zakupu za jednostkę'
            ])

            ->add('VAT', NumberType::class, [
                'label' => 'Stawka VAT (%)',
                'attr' => ['placeholder' => '23']
            ])

            ->add('documents', FileType::class, [
                'label' => 'Faktury / Dokumenty (PDF, XML)',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
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
        ]);
    }
}
