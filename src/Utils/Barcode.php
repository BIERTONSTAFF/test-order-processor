<?php

namespace Desq\TestOrderProcessor\Utils;

use Opis\ORM\EntityManager;
use Desq\TestOrderProcessor\Entities\{Ticket};
use Exception;

const MAX_GENERATE_ATTEMPTS = 10;
const BARCODE_RANDOM_MIN = 10000000;
const BARCODE_RANDOM_MAX = 99999999;

class Barcode
{
    /**
     * Генерация уникального barcode'а
     *
     * @param EntityManager $orm
     * @throws Exception
     * @return string
     */
    public static function generateUnique(EntityManager $orm): string
    {
        $attempt = 0;

        do {
            if ($attempt++ >= MAX_GENERATE_ATTEMPTS) {
                throw new Exception("Failed to generate unique barcode");
            }

            $barcode = sprintf(
                "%08d",
                random_int(BARCODE_RANDOM_MIN, BARCODE_RANDOM_MAX)
            );

            $exists = $orm
                ->query(Ticket::class)
                ->where("barcode")
                ->is($barcode)
                ->count();
        } while ($exists > 0);

        return $barcode;
    }
}
