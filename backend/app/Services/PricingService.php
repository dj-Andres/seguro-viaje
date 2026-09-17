<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class PricingService
{
    /**
     * Compute the number of travel days between two dates.
     */
    public function calculateDays(string $departure, string $return): int
    {
        $days = Carbon::parse($departure)->startOfDay()
            ->diffInDays(Carbon::parse($return)->startOfDay());

        if ($days < 1) {
            throw new InvalidArgumentException('La cantidad de días de viaje debe ser al menos 1.');
        }

        return $days;
    }

    /**
     * Calculate the quote breakdown for a given number of days and region.
     *
     * @return array{days: int, base_rate: float, surcharge_percentage: float, total: float}
     */
    public function calculate(int $days, string $region): array
    {
        $baseRatePerDay = (float) config('pricing.base_rate_per_day', 3.00);
        $surcharges = config('pricing.region_surcharges', []);

        $baseRate = round($days * $baseRatePerDay, 2);
        $surchargePercentage = (float) ($surcharges[$region] ?? 0);
        $total = round($baseRate * (1 + $surchargePercentage / 100), 2);

        return [
            'days' => $days,
            'base_rate' => $baseRate,
            'surcharge_percentage' => $surchargePercentage,
            'total' => $total,
        ];
    }
}
