<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', TextType::class)
            ->add('newPassword', PasswordType::class, [
                'label' => 'New Password',
                'mapped' => false,  // Not mapped to the entity
                'required' => false,  // Optional
            ])
            ->add('confirmMdp', PasswordType::class, [
                'label' => 'Confirm New Password',
                'mapped' => false, // Not mapped to the entity
                'required' => false,  // Optional
            ])
            ->add('oldPassword', PasswordType::class, [
                'label' => 'Old Password',
                'required' => true,  // Required to verify the old password
                'mapped' => false, // Not mapped to the entity
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}

