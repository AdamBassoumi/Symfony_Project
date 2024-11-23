<?php

namespace App\Form;

use App\Entity\Shop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Shop Name',
            'attr' => [
                'placeholder' => 'Enter your shop name',
                'class' => 'form-control', // Bootstrap class for styling
            ],
            'required' => true, // Validation rule (optional)
            'label_attr' => ['class' => 'form-label'], // Optional: Bootstrap label class
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Shop Description',
            'attr' => [
                'placeholder' => 'Describe your shop',
                'class' => 'form-control', // Bootstrap class for styling
            ],
            'required' => false, // Optional field
            'label_attr' => ['class' => 'form-label'], // Optional: Bootstrap label class
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Create Shop',
            'attr' => [
                'class' => 'btn btn-primary', // Bootstrap button class
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class, // Maps to the Shop entity
        ]);
    }
}
