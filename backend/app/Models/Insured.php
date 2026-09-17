<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insured extends Model
{
    use HasFactory;

    protected $table = 'asegurados';

    protected $fillable = [
        'nombres',
        'apellidos',
        'numero_identificacion',
        'correo_electronico',
        'fecha_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class, 'asegurado_id');
    }

    public function scopeSearch(Builder $query, ?string $text): Builder
    {
        if (! $text) {
            return $query;
        }

        $search = trim($text);

        return $query->where(function (Builder $query) use ($search) {
            $query
                ->where('nombres', 'like', "%{$search}%")
                ->orWhere('apellidos', 'like', "%{$search}%")
                ->orWhere('numero_identificacion', 'like', "%{$search}%")
                ->orWhere('correo_electronico', 'like', "%{$search}%");
        });
    }
}
