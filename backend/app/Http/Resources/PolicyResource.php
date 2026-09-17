<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asegurado' => new InsuredResource($this->whenLoaded('insured')),
            'pais_destino' => $this->pais_destino,
            'codigo_pais' => $this->codigo_pais,
            'region' => $this->region,
            'fecha_salida' => $this->fecha_salida?->format('Y-m-d'),
            'fecha_regreso' => $this->fecha_regreso?->format('Y-m-d'),
            'dias_viaje' => $this->dias_viaje,
            'tarifa_base' => (float) $this->tarifa_base,
            'porcentaje_recargo' => (float) $this->porcentaje_recargo,
            'valor_total' => (float) $this->valor_total,
            'estado' => $this->estado?->value,
            'fecha_contratacion' => $this->fecha_contratacion?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
