<?php

namespace Desq\TestOrderProcessor\Entities;

use DateMalformedStringException;
use Opis\ORM\{IEntityMapper, Entity, IMappableEntity};
use DateTime;

class Event extends Entity
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->orm()->getColumn("description");
    }

    /**
     * @return DateTime
     * @throws DateMalformedStringException
     */
    public function getDate(): DateTime
    {
        return new DateTime($this->orm()->getColumn("date"));
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
     * @return Event
     */
    public function setName(string $name): self
    {
        $this->orm()->setColumn("name", $name);

        return $this;
    }

    /**
     * @param string $description
     * @return Event
     */
    public function setDescription(string $description): self
    {
        $this->orm()->setColumn("description", $description);

        return $this;
    }

    /**
     * @param string $date
     * @return Event
     */
    public function setDate(string $date): self
    {
        $this->orm()->setColumn("date", $date);

        return $this;
    }
}
