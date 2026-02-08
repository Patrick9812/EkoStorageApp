<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, ['label' => 'Login'])
            ->add('fullname', null, ['label' => 'Imię i Nazwisko'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Pracownik' => 'ROLE_USER',
                    'Administrator' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Uprawnienia'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Hasła muszą być identyczne.',
                'required' => true,
                'first_options'  => ['label' => 'Hasło'],
                'second_options' => ['label' => 'Powtórz hasło'],
            ])
            ->add('warehouses', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Przypisane Magazyny'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Zarejestruj użytkownika'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
