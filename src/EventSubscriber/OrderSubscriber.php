<?php

namespace okpt\furnics\project\EventSubscriber;

use okpt\furnics\project\Event\OrderEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;

    public function __construct(WorkflowInterface $ordersProcessStateMachine, LoggerInterface $logger)
    {
        $this->workflow = $ordersProcessStateMachine;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvent::NAME => 'onOrderTransition',
        ];
    }

    public function onOrderTransition(OrderEvent $event): void
    {
        $order = $event->getOrder();
        $transitionName = $order->getNextTransition();

        $this->logger->info("Is possible to transit: ");
        $this->logger->debug($this->workflow->can($order, $transitionName));

        if ($this->workflow->can($order, $transitionName)) {
            $this->workflow->apply($order, $transitionName);
            $this->logger->info("Custom Log - Order manually transitioned to: $transitionName for Order ID: " . $order->getOrderId());
        } else {
            $this->logger->warning("Cannot transition order to: $transitionName from state: " . $order->getCurrentPlace());
        }
    }
}
