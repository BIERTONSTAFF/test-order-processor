<?php

namespace Tests\Services;

use Random\RandomException;
use Tests\TestCase;
use Desq\TestOrderProcessor\Services\ApiClient;

class ApiClientTest extends TestCase
{
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = new ApiClient();
    }

    /**
     * Тест оформления заказа
     *
     * @throws RandomException
     */
    public function testBookOrder(): void
    {
        $req = [
            "event_id" => 1,
            "event_date" => "1970-01-01 00:00:00",
            "ticket_price" => 1000,
            "barcode" => "10000000",
        ];

        $res = $this->apiClient->bookOrder($req);

        $this->assertIsArray($res);
        $this->assertTrue(isset($res["message"]) || isset($res["error"]));
    }

    /**
     * Тест подтверждения заказа
     *
     * @throws RandomException
     */
    public function testApproveOrder(): void
    {
        $res = $this->apiClient->approveOrder("10000000");

        $this->assertIsArray($res);
        $this->assertTrue(isset($res["message"]) || isset($res["error"]));
    }
}
