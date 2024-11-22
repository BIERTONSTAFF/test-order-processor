<?php

namespace Tests\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Desq\TestOrderProcessor\Utils\Barcode;

#[CoversClass(Barcode::class)]
class BarcodeTest extends TestCase
{
    public function testGenerateUniqueBarcode(): void
    {
        $a = Barcode::generateUnique($this->orm);
        $b = Barcode::generateUnique($this->orm);

        $this->assertNotEmpty($a);
        $this->assertNotEmpty($b);
        $this->assertNotEquals($a, $b);
    }
}
