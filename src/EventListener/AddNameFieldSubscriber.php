<?php

// src/Form/EventListener/AddNameFieldSubscriber.php

namespace  okpt\furnics\project\EventListener;

use okpt\furnics\project\Types\SizeAndQuantityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddNameFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public static function getSubscribedEvents2(): array
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event): void
    {
        $article = $event->getData();
        $form = $event->getForm();

        if (!$article || null === $article->getId()) {
            $form->add('SizeAndQuantityType', SizeAndQuantityType::class);
        }
    }
}
