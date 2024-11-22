<?php

namespace Desq\TestOrderProcessor\Services;

use Random\RandomException;

// const BASE_URL = "https://api.site.com";
const BOOK_CHANCE = 95;
const APPROVE_CHANCE = 80;

class ApiClient
{
    /**
     * Имитация оформления заказа
     *
     * https://api.site.com/book
     *
     * @param array<int,mixed> $req
     * @return array<string,string>
     * @throws RandomException
     */
    public function bookOrder(array $req): array
    {
        return random_int(1, 100) <= BOOK_CHANCE
            ? ["message" => "order successfully booked"]
            : ["error" => "barcode already exists"];
    }

    /**
     * Имитация подтверждения заказа
     *
     * https://api.site.com/approve
     *
     * @param string $barcode
     * @return array<string,string>
     * @throws RandomException
     */
    public function approveOrder(string $barcode): array
    {
        $res = [
            ["message" => "order successfully approved"],
            ["error" => "event cancelled"],
            ["error" => "no tickets"],
            ["error" => "no seats"],
            ["error" => "fan removed"],
        ];

        return random_int(1, 100) <= APPROVE_CHANCE
            ? $res[0]
            : $res[array_rand($res)];
    }
}
