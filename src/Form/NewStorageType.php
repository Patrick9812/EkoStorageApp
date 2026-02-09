<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewStorageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nazwa Magazynu'
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Przypisz Pracowników'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Utwórz magazyn'
            ]);

        $builder->add('users', EntityType::class, [
            'class' => User::class,
            'multiple' => true,
            'expanded' => true,
            'choice_label' => function (User $user) {
                $key = $_ENV['CRYPTO_KEY'] ?? 'twoj_domyslny_klucz';
                return $user->getDecryptedFullname($key);
            },
            'label' => 'PRZYPISZ PRACOWNIKÓW',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }
}
