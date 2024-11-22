<?php

namespace Desq\TestOrderProcessor\Entities;

use DateMalformedStringException;
use Opis\ORM\Entity;
use DateTime;

class Order extends Entity
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
     * @return string
     */
    public function getUserId(): string
    {
        return $this->orm()->getColumn("user_id");
    }

    /**
     * @return DateTime
     * @throws DateMalformedStringException
     */
    public function created(): DateTime
    {
        return new DateTime($this->orm()->getColumn("created"));
    }

    /**
     * @param int $id
     * @return Order
     */
    public function setEventId(int $id): self
    {
        $this->orm()->setColumn("event_id", $id);

        return $this;
    }

    /**
     * @param int $id
     * @return Order
     */
    public function setUserId(int $id): self
    {
        $this->orm()->setColumn("user_id", $id);

        return $this;
    }
}
