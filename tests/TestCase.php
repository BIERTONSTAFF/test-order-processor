<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Opis\Database\{Connection, Database};
use Opis\ORM\EntityManager;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected EntityManager $orm;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = new PDO(
            "pgsql:host=localhost;dbname=testorderprocessor",
            "postgres",
            "12345678"
        );

        $connection = Connection::fromPDO($pdo);
        $this->orm = new EntityManager($connection);

        $this->cleanDatabase();
    }

    protected function cleanDatabase(): void
    {
        $tables = [
            "tickets",
            "orders",
            "event_prices",
            "events",
            "ticket_types",
        ];

        foreach ($tables as $table) {
            $db = new Database($this->orm->getConnection());
            $db->from($table)->delete();
        }
    }
}
