<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    /**
     * Populate the local "paises" snapshot from the REST Countries v5 API.
     */
    public function run(): void
    {
        $countries = app(CountryService::class)->fetchFromApi();

        if ($countries->isEmpty()) {
            $this->command?->warn('No se pudieron obtener países de REST Countries v5. Se mantienen los registros existentes.');

            return;
        }

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['codigo' => $country['code']],
                [
                    'nombre' => $country['name'],
                    'region' => $country['region'],
                    'subregion' => $country['subregion'],
                    'bandera' => $country['flag'],
                ],
            );
        }

        Log::info('CountrySeeder: países sincronizados desde REST Countries v5.', [
            'total' => $countries->count(),
        ]);
    }
}