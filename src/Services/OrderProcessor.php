<?php

namespace Desq\TestOrderProcessor\Services;

use DateMalformedStringException;
use Exception;
use Desq\TestOrderProcessor\Entities\{
    Order,
    Event,
    EventPrice,
    Ticket,
    TicketType
};
use Desq\TestOrderProcessor\Exceptions\{
    EntityNotFoundException,
    EntityCreationException,
    ApiException
};
use Desq\TestOrderProcessor\Utils\Barcode;
use Opis\ORM\EntityManager;
use Random\RandomException;

class OrderProcessor
{
    private ApiClient $apiClient;
    private EntityManager $orm;

    public function __construct(EntityManager $orm)
    {
        $this->apiClient = new ApiClient();
        $this->orm = $orm;
    }

    /**
     * @param int $eventId
     * @return Event
     * @throws EntityNotFoundException
     */
    public function getEvent(int $eventId): Event
    {
        $event = $this->orm
            ->query(Event::class)
            ->where("id")
            ->is($eventId)
            ->get();

        if (!$event) {
            throw new EntityNotFoundException("Event not found");
        }

        return $event;
    }

    /**
     * @param string $name
     * @return TicketType
     * @throws EntityNotFoundException
     */
    public function getTicketType(string $name): TicketType
    {
        $ticketType = $this->orm
            ->query(TicketType::class)
            ->where("name")
            ->is($name)
            ->get();

        if (!$ticketType) {
            throw new EntityNotFoundException(
                "Ticket type not found: " . $name
            );
        }

        return $ticketType;
    }

    /**
     * Получение актуальной цены билеты по типу
     *
     * @param int $eventId
     * @param int $ticketTypeId
     * @return EventPrice
     * @throws EntityNotFoundException
     */
    public function getActualPrice(int $eventId, int $ticketTypeId): EventPrice
    {
        $actualPrice = $this->orm
            ->query(EventPrice::class)
            ->where("event_id")
            ->is($eventId)
            ->where("valid_to")
            ->isNull()
            ->get();

        if (!$actualPrice) {
            throw new EntityNotFoundException(
                "No valid price found for the event"
            );
        }

        return $actualPrice;
    }

    /**
     * Создание заказа
     *
     * @param int $eventId
     * @param string $ticketTypeName
     * @param int $ticketQuantity
     * @return ?Order
     * @throws ApiException
     * @throws EntityCreationException
     * @throws EntityNotFoundException
     * @throws RandomException
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public function createOrder(
        int $eventId,
        string $ticketTypeName,
        int $ticketQuantity
    ): ?Order {
        return $this->orm
            ->getConnection()
            ->transaction(function () use (
                $eventId,
                $ticketTypeName,
                $ticketQuantity
            ) {
                $event = $this->getEvent($eventId);
                $ticketType = $this->getTicketType($ticketTypeName);
                $actualPrice = $this->getActualPrice(
                    $eventId,
                    $ticketType->id()
                );

                $barcodes = [];

                for ($i = 0; $i < $ticketQuantity; $i++) {
                    $barcodes[$i] = Barcode::generateUnique($this->orm);
                }

                foreach ($barcodes as $barcode) {
                    $req = [
                        "event_id" => $eventId,
                        "event_date" => $event->getDate(),
                        "ticket_price" => $actualPrice->getPrice(),
                        "barcode" => $barcode,
                    ];

                    $res = $this->apiClient->bookOrder($req);
                    if (isset($res["error"])) {
                        throw new ApiException($res["error"]);
                    }

                    $res = $this->apiClient->approveOrder($barcode);
                    if (isset($res["error"])) {
                        throw new ApiException($res["error"]);
                    }
                }

                /** @var Order $order */
                $order = $this->orm->create(Order::class);
                $order->setEventId($eventId)->setUserId(random_int(1, 100));
                $this->orm->save($order);

                if (!$order->id()) {
                    throw new EntityCreationException("Failed to create order");
                }

                foreach ($barcodes as $barcode) {
                    $this->createTicket($order, $ticketType->id(), $barcode);
                }

                return $order;
            });
    }

    /**
     * Создание билета
     *
     * @param Order $order
     * @param int $ticketTypeId
     * @param string $barcode
     * @return void
     * @throws EntityCreationException|EntityNotFoundException
     */
    public function createTicket(
        Order $order,
        int $ticketTypeId,
        string $barcode
    ): void {
        /** @var Ticket $ticket */
        $ticket = $this->orm->create(Ticket::class);
        $actualPrice = $this->getActualPrice(
            $order->getEventId(),
            $ticketTypeId
        );

        $ticket
            ->setOrderId($order->id())
            ->setEventPriceId($actualPrice->id())
            ->setBarcode($barcode);

        $this->orm->save($ticket);

        if (!$ticket->id()) {
            throw new EntityCreationException("Failed to create ticket");
        }
    }

    /**
     * Получение билетов заказа
     *
     * @param int $orderId
     * @return array
     * @throws EntityNotFoundException
     */
    public function getTickets(int $orderId): array
    {
        $tickets = $this->orm
            ->query(Ticket::class)
            ->where("order_id")
            ->is($orderId)
            ->all();

        if (empty($tickets)) {
            throw new EntityNotFoundException(
                "No tickets found for specified order: " . $orderId
            );
        }

        return $tickets;
    }
}
