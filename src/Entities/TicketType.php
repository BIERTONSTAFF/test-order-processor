<?php

namespace Desq\TestOrderProcessor\Entities;

use DateMalformedStringException;
use Opis\ORM\Entity;
use DateTime;

class TicketType extends Entity
{
    /**
     * @return int
     */
    public function id(): int
    {
        return $this->orm()->getColumn("id");
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->orm()->getColumn("name");
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
     * @param string $name
     * @return TicketType
     */
    public function setName(string $name): self
    {
        $this->orm()->setColumn("name", $name);

        return $this;
    }
}
