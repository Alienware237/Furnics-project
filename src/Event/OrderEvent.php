<?php

namespace okpt\furnics\project\Event;

use JMS\Serializer\EventDispatcher\Event;
use okpt\furnics\project\Entity\Orders;

class OrderEvent extends Event
{
    public const NAME = 'order.transition';

    private Orders $order;

    public function __construct(Orders $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Orders
    {
        return $this->order;
    }
}
