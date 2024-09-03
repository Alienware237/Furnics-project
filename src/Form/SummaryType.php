<?php

namespace okpt\furnics\project\Form;

use Doctrine\Common\Collections\Order;
use okpt\furnics\project\Entity\Orders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Order Date',
                'attr' => ['readonly' => true],
            ])
            ->add('country', TextType::class, [
                'label' => 'State',
                'attr' => ['readonly' => true],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['readonly' => true],
            ])
            ->add('street', TextType::class, [
                'label' => 'Street',
                'attr' => ['readonly' => true],
            ])
            ->add('HouseNumber', NumberType::class, [
                'label' => 'HouseNumber',
                'attr' => ['readonly' => true],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => ['readonly' => true],
            ])
            ->add('name', TextType::class, [
                'label' => 'User',
                //'mapped' => false,
                //'data' => $options['user_name'],
                'attr' => ['readonly' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
            'user_name' => null,
        ]);
    }
}
