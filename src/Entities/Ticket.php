<?php

namespace Desq\TestOrderProcessor\Entities;

use DateMalformedStringException;
use Opis\ORM\Entity;
use DateTime;

class Ticket extends Entity
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
    public function getOrderId(): int
    {
        return $this->orm()->getColumn("order_id");
    }

    /**
     * @return int
     */
    public function getEventPriceId(): int
    {
        return $this->orm()->getColumn("event_price_id");
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->orm()->getColumn("barcode");
    }

    /**
     * @return bool
     */
    public function getUsed(): bool
    {
        return $this->orm()->getColumn("used");
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
     * @return Ticket
     */
    public function setOrderId(int $id): self
    {
        $this->orm()->setColumn("order_id", $id);

        return $this;
    }

    /**
     * @param int $id
     * @return Ticket
     */
    public function setEventPriceId(int $id): self
    {
        $this->orm()->setColumn("event_price_id", $id);

        return $this;
    }

    /**
     * @param string $barcode
     * @return Ticket
     */
    public function setBarcode(string $barcode): self
    {
        $this->orm()->setColumn("barcode", $barcode);

        return $this;
    }

    /**
     * @param bool $bool
     * @return Ticket
     */
    public function setUsed(bool $bool): self
    {
        $this->orm()->setColumn("used", $bool);

        return $this;
    }
}
