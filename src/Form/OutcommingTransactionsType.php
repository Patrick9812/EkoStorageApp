<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutcommingTransactionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'name',
                'label' => 'Artykuł do wydania',
                'placeholder' => 'Wybierz produkt...',
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Ilość wydawana',
                'html5' => true,
                'attr' => [
                    'step' => '0.001',
                    'placeholder' => '0.000'
                ]
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
