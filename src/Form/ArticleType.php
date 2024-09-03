<?php

namespace okpt\furnics\project\Form;

use okpt\furnics\project\Entity\Article;
use okpt\furnics\project\Types\SizeAndQuantityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('articleName', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'required' => true,
            ])
            ->add('articlePrice', NumberType::class, [
                'required' => true,
            ])
            ->add('articleCategory', TextType::class, [
                'required' => true,
            ])
            ->add('categoryDescription', TextType::class, [
                'required' => true,
            ])
            ->add('sizeAndQuantities', CollectionType::class, [
                'entry_type' => SizeAndQuantityType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            // ...
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
