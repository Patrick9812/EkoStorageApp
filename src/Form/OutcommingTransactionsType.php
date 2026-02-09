<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class OutcommingTransactionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'label' => 'Z magazynu',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-blue-500 transition-all'],
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('w')
                        ->innerJoin('w.users', 'u')
                        ->where('u.id = :userId')
                        ->setParameter('userId', $user->getId());
                },
            ])

            ->add('code', TextType::class, [
                'label' => 'Kod wydania',
                'attr' => ['placeholder' => 'Wpisz kod partii, którą wydajesz']
            ])
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'name',
                'label' => 'Artykuł',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-blue-500 transition-all'],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Ilość do wydania',
                'attr' => [
                    'step' => '0.001',
                    'placeholder' => '0.000',
                    'class' => 'w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-blue-500 transition-all'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
            'validation_groups' => ['Default'],
        ]);
    }
}
