<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuredResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'numero_identificacion' => $this->numero_identificacion,
            'correo_electronico' => $this->correo_electronico,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
        ];
    }
}
