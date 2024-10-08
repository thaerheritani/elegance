<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Size;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productType', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Type de produit',
                'placeholder' => 'Choisir un type',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.type = :type')
                        ->setParameter('type', 'product_type');
                },
                'required' => false,
                'attr' => ['class' => 'form-select form-select-lg mb-3'] // Large select with margin
            ])
            ->add('target', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Cible',
                'placeholder' => 'Choisir une cible',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.type = :type')
                        ->setParameter('type', 'target');
                },
                'required' => false,
                'attr' => ['class' => 'form-select form-select-lg mb-3'] // Large select with margin
            ])
            ->add('size', EntityType::class, [
                'class' => Size::class,
                'choice_label' => 'name',
                'label' => 'Taille',
                'placeholder' => 'Choisir une taille',
                'required' => false,
                'attr' => ['class' => 'form-select form-select-lg mb-3'] // Large select with margin
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary btn-lg btn-block'] // Full-width button
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
