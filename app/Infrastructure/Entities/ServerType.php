<?php

namespace App\Infrastructure\Entities;

use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class ServerType
{
    public readonly string $name;

    public function __construct(
        public readonly string $id,
        public readonly int $cpuCores,
        public readonly int $memoryInMb,
        public readonly int $storageInGb,
        public readonly null|int $monthlyPriceAmount = null,
        public readonly null|string $monthlyPriceCurrency = null,
    ) {

        $memoryInGb = $this->memoryInMb / 1024;

        $name = "{$this->id}: {$this->cpuCores} CPU, {$memoryInGb} GB RAM, {$this->storageInGb} GB";

        if ($monthlyPriceAmount && $monthlyPriceCurrency) {
            $money = new Money($monthlyPriceAmount, new Currency($monthlyPriceCurrency));

            $name .= ' ('.app(IntlMoneyFormatter::class)->format($money).'/month)';
        }

        $this->name = $name;
    }
}
