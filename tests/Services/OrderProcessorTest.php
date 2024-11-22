<?php

namespace Tests\Services;

use DateMalformedStringException;
use Desq\TestOrderProcessor\Exceptions\ApiException;
use Desq\TestOrderProcessor\Exceptions\EntityCreationException;
use Desq\TestOrderProcessor\Exceptions\EntityNotFoundException;
use Random\RandomException;
use Tests\TestCase;
use Desq\TestOrderProcessor\Services\OrderProcessor;
use Desq\TestOrderProcessor\Entities\{
    Event,
    Ticket,
    TicketType,
    EventPrice
};
use Exception;

class OrderProcessorTest extends TestCase
{
    private OrderProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new OrderProcessor($this->orm);
    }

    /**
     * Тест создания цены события
     *
     * @param Event $event
     * @param TicketType $ticketType
     * @return void
     */
    private function createTestEventPrice(
        Event $event,
        TicketType $ticketType
    ): void
    {
        $eventPrice = $this->orm->create(EventPrice::class);

        $eventPrice
            ->setEventId($event->id())
            ->setTicketTypeId($ticketType->id())
            ->setPrice(1000);
        $this->orm->save($eventPrice);
    }

    /**
     * Тест создания типа билета
     *
     * @return TicketType
     */
    private function createTestTicketType(): TicketType
    {
        $ticketType = $this->orm->create(TicketType::class);

        $ticketType->setName("test");
        $this->orm->save($ticketType);

        return $ticketType;
    }

    /**
     * Тест создания события
     *
     * @return Event
     */
    private function createTestEvent(): Event
    {
        $event = $this->orm->create(Event::class);

        $event
            ->setName("Test Event")
            ->setDescription("Test Description")
            ->setDate("1970-01-01 00:00:00");
        $this->orm->save($event);

        return $event;
    }

    /**
     * Тест создания заказа к некорректному событию
     *
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws EntityNotFoundException
     * @throws EntityCreationException
     * @throws ApiException
     */
    public function testCreateOrderWithInvalidEvent(): void
    {
        $this->expectException(Exception::class);
        $this->processor->createOrder(-1, "test", 1);
    }

    /**
     * Тест создания заказа
     *
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws EntityNotFoundException
     * @throws EntityCreationException
     * @throws ApiException
     */
    public function testCreateOrder(): void
    {
        $event = $this->createTestEvent();
        $ticketType = $this->createTestTicketType();

        $this->createTestEventPrice($event, $ticketType);

        $order = $this->processor->createOrder(
            $event->id(),
            $ticketType->getName(),
            1
        );

        $this->assertNotNull($order);
        $this->assertEquals($event->id(), $order->getEventId());

        $tickets = $this->orm
            ->query(Ticket::class)
            ->where("order_id")
            ->is($order->id())
            ->all();

        $this->assertNotEmpty($tickets);

        foreach ($tickets as $ticket) {
            $this->assertNotEmpty($ticket->getBarcode());
            $this->assertFalse($ticket->getUsed());
        }
    }
}
