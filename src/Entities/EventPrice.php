<?php

namespace Desq\TestOrderProcessor\Entities;

use DateMalformedStringException;
use Opis\ORM\Entity;
use DateTime;

class EventPrice extends Entity
{
    /**
     * @return int
     */
    public function id(): int
    {
        return $this->orm()->getColumn("id");
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->orm()->getColumn("event_id");
    }

    /**
     * @return int
     */
    public function getTicketTypeId(): int
    {
        return $this->orm()->getColumn("ticket_type_id");
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->orm()->getColumn("price");
    }

    /**
     * @return DateTime
     * @throws DateMalformedStringException
     */
    public function getValidTo(): DateTime
    {
        return new DateTime($this->orm()->getColumn("valid_to"));
    }

    /**
     * @return DateTime
     */
    public function created(): DateTime
    {
        return $this->orm()->getColumn("created");
    }

    /**
     * @param int $id
     * @return EventPrice
     */
    public function setEventId(int $id): self
    {
        $this->orm()->setColumn("event_id", $id);

        return $this;
    }

    /**
     * @param int $id
     * @return EventPrice
     */
    public function setTicketTypeId(int $id): self
    {
        $this->orm()->setColumn("ticket_type_id", $id);

        return $this;
    }

    /**
     * @param int $price
     * @return EventPrice
     */
    public function setPrice(int $price): self
    {
        $this->orm()->setColumn("price", $price);

        return $this;
    }

    /**
     * @param string $date
     * @return EventPrice
     */
    public function setValidTo(string $date): self
    {
        $this->orm()->setColumn("valid_to", $date);

        return $this;
    }
}
