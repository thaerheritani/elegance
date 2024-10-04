<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeAddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Street',
                'attr' => ['autocomplete' => 'street-address'],
                'mapped' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['autocomplete' => 'address-level2'],
                'mapped' => false,
            ])
            ->add('postalCode', IntegerType::class, [
                'label' => 'Postal Code',
                'attr' => ['autocomplete' => 'postal-code'],
                'mapped' => false,
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'attr' => ['autocomplete' => 'country'],
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
