<?php

namespace okpt\furnics\project\EventListener;

use okpt\furnics\project\Event\OrderEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderTrasitionListener
{
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;

    public function __construct(WorkflowInterface $ordersProcessStateMachine, LoggerInterface $logger)
    {
        $this->workflow = $ordersProcessStateMachine;
        $this->logger = $logger;
    }

    #[AsEventListener(event: OrderEvent::NAME)]
    public function onOrderTransition(OrderEvent $event)
    {
        $order = $event->getOrder();
        $transitionName = $order->getNextTransition();
        $currentState = $order->getCurrentPlace();

        $this->logger->info("Attempting to transition from: $currentState to: $transitionName");

        if ($this->workflow->can($order, $transitionName)) {
            $this->workflow->apply($order, $transitionName);
            $this->logger->info("Order transitioned to: $transitionName");
        } else {
            $this->logger->warning("Cannot transition order to: $transitionName from: $currentState");
        }
    }
}