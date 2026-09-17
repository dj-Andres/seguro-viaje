<?php

namespace App\Services;

use App\Enums\PolicyStatus;
use App\Models\Insured;
use App\Models\Policy;
use Illuminate\Validation\ValidationException;

class QuoteService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly CountryService $countries,
    ) {
    }

    /**
     * Persist a new quotation (policy in "cotizado" state).
     */
    public function create(array $data): Policy
    {
        $country = $this->countries->findByCode($data['codigo_pais']);

        if ($country === null) {
            throw ValidationException::withMessages([
                'codigo_pais' => ['El país seleccionado no es válido.'],
            ]);
        }

        $insured = Insured::query()->firstOrCreate(
            ['numero_identificacion' => $data['numero_identificacion']],
            [
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'correo_electronico' => $data['correo_electronico'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
            ],
        );

        $region = $this->countries->pricingRegion($country);
        $days = $this->pricing->calculateDays($data['fecha_salida'], $data['fecha_regreso']);
        $quote = $this->pricing->calculate($days, $region);

        return Policy::query()->create([
            'asegurado_id' => $insured->id,
            'pais_destino' => $country['name'],
            'codigo_pais' => strtoupper($data['codigo_pais']),
            'region' => $region,
            'fecha_salida' => $data['fecha_salida'],
            'fecha_regreso' => $data['fecha_regreso'],
            'dias_viaje' => $quote['days'],
            'tarifa_base' => $quote['base_rate'],
            'porcentaje_recargo' => $quote['surcharge_percentage'],
            'valor_total' => $quote['total'],
            'estado' => PolicyStatus::Quoted,
        ]);
    }

    /**
     * Confirm the contract of an existing quotation.
     */
    public function contract(Policy $policy): Policy
    {
        $policy->update([
            'estado' => PolicyStatus::Contracted,
            'fecha_contratacion' => now(),
        ]);

        return $policy->refresh();
    }
}
