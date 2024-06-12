<?php

namespace okpt\furnics\project\Form;

use okpt\furnics\project\Services\AddressChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryAddressType extends AbstractType
{
    private $adressChecker;

    public function __construct(AddressChecker $adressChecker)
    {
        $this->adressChecker = $adressChecker;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $order = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class, [
                'data' => $order ? $order->getName() : '', // Initial value
            ])
            ->add('phone', TelType::class, [
                'data' => $order ? $order->getPhone() : '', // Initial value
            ])
            ->add('email', EmailType::class, [
                'data' => $order ? $order->getEmail() : '', // Initial value
            ])
            ->add('country', TextType::class, [
                'data' => $order ? $order->getCountry() : '', // Initial value
            ])
            ->add('city', TextType::class, [
                'data' => $order ? $order->getCity() : '', // Initial value
            ])
            ->add('street', TextType::class, [
                'data' => $order ? $order->getStreet() : '', // Initial value
            ])
            ->add('houseNumber', IntegerType::class, [
                'data' => $order ? $order->getHouseNumber() : '', // Initial value
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $order = $event->getData();
            $form = $event->getForm();

            if ($order && $this->isEU($order->getCountry())) {
                $form->add('taxNumber', TextType::class, [
                    'required' => true,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['country']) && $this->isEU($data['country'])) {
                $form->add('taxNumber', TextType::class, [
                    'required' => true,
                ]);
            }
        });
    }

    private function isEU($address): bool
    {
        // Implement logic to check if the address is in a European Union member state
        return $this->adressChecker->isEU($address);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
