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
                'class' => 'form-control',
            ],
            'required' => true,
            'label_attr' => ['class' => 'form-label'],
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Shop Description',
            'attr' => [
                'placeholder' => 'Describe your shop',
                'class' => 'form-control',
            ],
            'required' => false, // Optional field
            'label_attr' => ['class' => 'form-label'],
        ])
        ->add('submit', SubmitType::class, [
            'label' => $options['submit_label'] ?? 'Create Shop',
            'attr' => [
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
            'submit_label' => 'Create Shop',  // Default label is 'Create Shop'
        ]);
    }
}
