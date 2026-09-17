<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Policy extends Model
{
    use HasFactory;

    public const STATUS_QUOTED = 'cotizado';

    public const STATUS_CONTRACTED = 'contratado';

    protected $table = 'polizas';

    protected $fillable = [
        'asegurado_id',
        'pais_destino',
        'codigo_pais',
        'region',
        'fecha_salida',
        'fecha_regreso',
        'dias_viaje',
        'tarifa_base',
        'porcentaje_recargo',
        'valor_total',
        'estado',
        'fecha_contratacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_salida' => 'date',
            'fecha_regreso' => 'date',
            'fecha_contratacion' => 'datetime',
            'tarifa_base' => 'decimal:2',
            'porcentaje_recargo' => 'decimal:2',
            'valor_total' => 'decimal:2',
        ];
    }

    public function insured(): BelongsTo
    {
        return $this->belongsTo(Insured::class, 'asegurado_id');
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return $status
            ? $query->where('estado', $status)
            : $query;
    }

    public function scopeByDestination(Builder $query, ?string $destination): Builder
    {
        if (! $destination) {
            return $query;
        }

        return $query->where('pais_destino', 'like', '%'.trim($destination).'%');
    }

    public function scopeByIdentification(Builder $query, ?string $identification): Builder
    {
        if (! $identification) {
            return $query;
        }

        return $query->whereHas('insured', function (Builder $query) use ($identification) {
            $query->where('numero_identificacion', 'like', '%'.trim($identification).'%');
        });
    }

    public function scopeByClient(Builder $query, ?string $text): Builder
    {
        if (! $text) {
            return $query;
        }

        $search = trim($text);

        return $query->whereHas('insured', function (Builder $query) use ($search) {
            $query
                ->where('nombres', 'like', "%{$search}%")
                ->orWhere('apellidos', 'like', "%{$search}%");
        });
    }

    public function scopeByDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('fecha_salida', '>=', $from);
        }

        if ($to) {
            $query->whereDate('fecha_salida', '<=', $to);
        }

        return $query;
    }
}
