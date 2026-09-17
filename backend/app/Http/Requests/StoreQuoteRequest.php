<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minimumBirthDate = now()->subYears(18)->format('Y-m-d');

        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'numero_identificacion' => ['required', 'string', 'max:20'],
            'correo_electronico' => ['required', 'email', 'max:255'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.$minimumBirthDate],
            'codigo_pais' => ['required', 'string', 'size:2'],
            'fecha_salida' => ['required', 'date', 'after_or_equal:today'],
            'fecha_regreso' => ['required', 'date', 'after:fecha_salida'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El campo nombres es obligatorio.',
            'nombres.string' => 'El campo nombres debe ser un texto válido.',
            'nombres.max' => 'El campo nombres no debe superar los 255 caracteres.',

            'apellidos.required' => 'El campo apellidos es obligatorio.',
            'apellidos.string' => 'El campo apellidos debe ser un texto válido.',
            'apellidos.max' => 'El campo apellidos no debe superar los 255 caracteres.',

            'numero_identificacion.required' => 'El campo número de identificación es obligatorio.',
            'numero_identificacion.string' => 'El campo número de identificación debe ser un texto válido.',
            'numero_identificacion.max' => 'El campo número de identificación no debe superar los 20 caracteres.',

            'correo_electronico.required' => 'El campo correo electrónico es obligatorio.',
            'correo_electronico.email' => 'El campo correo electrónico debe ser una dirección de correo válida.',
            'correo_electronico.max' => 'El campo correo electrónico no debe superar los 255 caracteres.',

            'fecha_nacimiento.required' => 'El campo fecha de nacimiento es obligatorio.',
            'fecha_nacimiento.date' => 'El campo fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'Debes ser mayor de 18 años para contratar el seguro.',

            'codigo_pais.required' => 'El campo país de destino es obligatorio.',
            'codigo_pais.string' => 'El campo país de destino debe ser un código válido.',
            'codigo_pais.size' => 'El código de país debe tener 2 caracteres.',

            'fecha_salida.required' => 'El campo fecha de salida es obligatorio.',
            'fecha_salida.date' => 'El campo fecha de salida debe ser una fecha válida.',
            'fecha_salida.after_or_equal' => 'La fecha de salida no puede ser anterior a hoy.',

            'fecha_regreso.required' => 'El campo fecha de regreso es obligatorio.',
            'fecha_regreso.date' => 'El campo fecha de regreso debe ser una fecha válida.',
            'fecha_regreso.after' => 'La fecha de regreso debe ser posterior a la fecha de salida.',
        ];
    }
}
