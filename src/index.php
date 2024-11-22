<?php

namespace Desq\TestOrderProcessor;

require "vendor/autoload.php";

use Opis\Database\{Connection, Database};
use DateMalformedStringException;
use Opis\ORM\EntityManager;
use Desq\TestOrderProcessor\Services\OrderProcessor;
use Desq\TestOrderProcessor\Entities\{Event, EventPrice, TicketType, Order};
use Desq\TestOrderProcessor\Exceptions\{EntityNotFoundException,
    FsException,
    ValidationException,
    DatabaseException,
    EntityCreationException,
    ApiException};
use PDO;
use DateTime;
use Exception;
use DateTimeImmutable;
use Random\RandomException;

const USAGE = <<<EOT
USAGE:
    %s EVENT_NAME TICKET_TYPE QUANTITY
    %s --create-event EVENT_NAME DESCRIPTION EVENT_DATE TICKET_TYPE PRICE QUANTITY
EOT;
const DATE_FORMAT = "Y-m-d H:i:s";

if ($argc < 4) {
    printf(USAGE . PHP_EOL, $argv[0], $argv[0]);
    exit(1);
}

/**
 * @throws FsException
 */
function loadDbConfig(): array
{
    $file = file_get_contents(__DIR__ . "/../dbConfig.json");

    if (!$file) {
        throw new FsException("Could not read dbConfig.json");
    }

    return json_decode($file, true);
}

/**
 * @param string $date
 * @return bool
 */
function validateDate(string $date): bool
{
    /** @var DateTime $dateTime */
    $dateTime = DateTimeImmutable::createFromFormat(DATE_FORMAT, $date);

    return $dateTime && $dateTime->format(DATE_FORMAT) === $date;
}

/**
 * Инициализация соединения с БД
 *
 * @throws DatabaseException
 * @return EntityManager
 */
function getOrm(): EntityManager
{
    try {
        $dbConfig = loadDbConfig();
        $pdo = new PDO(
            sprintf("pgsql:host=%s;dbname=%s", $dbConfig['host'], $dbConfig['dbName']),
            $dbConfig['user'],
            $dbConfig['password']
        );

        $connection = Connection::fromPDO($pdo);

        return new EntityManager($connection);
    } catch (Exception $e) {
        throw new DatabaseException($e->getMessage());
    }
}

/**
 * Обработка аргументов создания события
 *
 * @param array $args
 * @return array<string,string|int>
 * @throws ValidationException
 */
function handleCreateEventArgs(array $args): array
{
    if (count($args) !== 8) {
        throw new ValidationException(
            "Error: --create-event requires EVENT_NAME, DESCRIPTION, EVENT_DATE, TICKET_TYPE, PRICE, QUANTITY arguments"
        );
    }

    $eventDate = $args[4];

    if (!validateDate($eventDate)) {
        throw new ValidationException(
            sprintf("Invalid date format. Use format: %s", DATE_FORMAT)
        );
    }

    return [
        "eventName" => $args[2],
        "eventDescription" => $args[3],
        "eventDate" => $args[4],
        "ticketTypeName" => $args[5],
        "ticketTypePrice" => (int) $args[6],
        "ticketQuantity" => (int) $args[7],
    ];
}

/**
 * Обработка регулярных аргументов
 *
 * @param array $args
 * @return array<string,string|int>
 * @throws ValidationException
 */
function handleRegularArgs(array $args): array
{
    if (count($args) !== 4) {
        throw new ValidationException(
            "Error: Regular mode requires EVENT_NAME, TICKET_TYPE and TICKET_QUANTITY arguments"
        );
    }

    return [
        "eventName" => $args[1],
        "eventDate" => "",
        "eventDescription" => "",
        "ticketTypeName" => $args[2],
        "ticketTypePrice" => 0,
        "ticketQuantity" => (int) $args[3],
    ];
}

try {
    $params =
        $argv[1] === "--create-event"
            ? handleCreateEventArgs($argv)
            : handleRegularArgs($argv);

    $orm = getOrm();
    $processor = new OrderProcessor($orm);

    /**
     * Поиск существующего события
     *
     * @var Event $event
     */
    $event = $orm
        ->query(Event::class)
        ->where("name")
        ->is($params["eventName"])
        ->get();

    if (!$event) {
        if (empty($params["eventDate"])) {
            throw new EntityNotFoundException("Specified event does not exist");
        }
        /**
         * Создание события, в случае отсутствия
         *
         * @var Event $event
         */
        $event = $orm->create(Event::class);

        $event
            ->setName($params["eventName"])
            ->setDescription($params["eventDescription"])
            ->setDate($params["eventDate"]);

        $orm->save($event);

        if (!$event->id()) {
            throw new EntityCreationException("Failed to create event");
        }
    }

    /**
     * Поиск существующего типа билета
     *
     * @var TicketType $ticketType
     */
    $ticketType = $orm
        ->query(TicketType::class)
        ->where("name")
        ->is($params["ticketTypeName"])
        ->get();

    if (!$ticketType) {
        /**
         * Создание типа билета, в случае отсутствия
         *
         * @var TicketType $ticketType
         */
        $ticketType = $orm->create(TicketType::class);

        $ticketType->setName($params["ticketTypeName"]);
        $orm->save($ticketType);

        if (!$ticketType->id()) {
            throw new EntityCreationException("Failed to create ticket type");
        }
    }

    /**
     * Поиск существующей цены билета
     *
     * @var EventPrice $eventPrice
     */
    $eventPrice = $orm
        ->query(EventPrice::class)
        ->where("event_id")
        ->is($event->id())
        ->where("ticket_type_id")
        ->is($ticketType->id())
        ->where("valid_to")
        ->isNull()
        ->get();

    if (
        $eventPrice &&
        $eventPrice->getPrice() !== $params["ticketTypePrice"]
    ) {
        $eventPrice->setValidTo((new DateTime())->format(DATE_FORMAT));
        $orm->save($eventPrice);
        $eventPrice = null;
    }

    if (!$eventPrice) {
        /**
         * Создание цены билета, в случае отсутствия
         *
         * @var EventPrice $eventPrice
         */
        $eventPrice = $orm->create(EventPrice::class);
        $eventPrice
            ->setEventId($event->id())
            ->setTicketTypeId($ticketType->id())
            ->setPrice($params["ticketTypePrice"]);
        $orm->save($eventPrice);

        if (!$eventPrice->id()) {
            throw new EntityCreationException("Failed to create event price");
        }
    }

    $order = $processor->createOrder(
        eventId: $event->id(),
        ticketTypeName: $params["ticketTypeName"],
        ticketQuantity: $params["ticketQuantity"]
    );

    $tickets = $processor->getTickets($order->id());

    echo "Order " .
        $order->id() .
        " created successfully with tickets (barcodes):" .
        PHP_EOL;
    foreach ($tickets as $ticket) {
        echo "- " . $ticket->getBarCode() . PHP_EOL;
    }
} catch (
    ValidationException |
    ApiException |
    DatabaseException |
    EntityCreationException |
    EntityNotFoundException |
    DateMalformedStringException |
    RandomException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(1);
} catch (Exception $e) {
    echo $e->getMessage() . $e->getTraceAsString() . PHP_EOL;
}

