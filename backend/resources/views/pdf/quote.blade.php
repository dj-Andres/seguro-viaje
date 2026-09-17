<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $policy->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; margin: 0; padding: 24px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .subtitle { color: #6b7280; margin-bottom: 24px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
        .card-title { background: #f3f4f6; padding: 10px 16px; font-weight: bold; font-size: 14px; }
        .card-body { padding: 12px 16px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        .label { color: #6b7280; width: 200px; }
        .total-row { font-size: 18px; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; }
        .badge-quoted { background: #fef3c7; color: #92400e; }
        .badge-contracted { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <h1>Cotización de Seguro de Viaje</h1>
    <div class="subtitle">N.º {{ $policy->id }} — Generada el {{ $policy->created_at->format('d/m/Y H:i') }}</div>

    <div class="card">
        <div class="card-title">Datos del asegurado</div>
        <div class="card-body">
            <table>
                <tr><td class="label">Nombres</td><td>{{ $policy->insured->nombres }}</td></tr>
                <tr><td class="label">Apellidos</td><td>{{ $policy->insured->apellidos }}</td></tr>
                <tr><td class="label">Identificación</td><td>{{ $policy->insured->numero_identificacion }}</td></tr>
                <tr><td class="label">Correo electrónico</td><td>{{ $policy->insured->correo_electronico }}</td></tr>
                <tr><td class="label">Fecha de nacimiento</td><td>{{ $policy->insured->fecha_nacimiento->format('d/m/Y') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Datos del viaje</div>
        <div class="card-body">
            <table>
                <tr><td class="label">País de destino</td><td>{{ $policy->pais_destino }} ({{ $policy->codigo_pais }})</td></tr>
                <tr><td class="label">Región</td><td>{{ $policy->region }}</td></tr>
                <tr><td class="label">Fecha de salida</td><td>{{ $policy->fecha_salida->format('d/m/Y') }}</td></tr>
                <tr><td class="label">Fecha de regreso</td><td>{{ $policy->fecha_regreso->format('d/m/Y') }}</td></tr>
                <tr><td class="label">Cantidad de días</td><td>{{ $policy->dias_viaje }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Detalle de la cotización</div>
        <div class="card-body">
            <table>
                <tr><td class="label">Tarifa base (USD 3/día)</td><td>$ {{ number_format($policy->tarifa_base, 2) }}</td></tr>
                <tr><td class="label">Recargo ({{ $policy->porcentaje_recargo }}%)</td><td>$ {{ number_format($policy->tarifa_base * $policy->porcentaje_recargo / 100, 2) }}</td></tr>
                <tr class="total-row"><td class="label">Valor total</td><td>$ {{ number_format($policy->valor_total, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            Estado:
            @if ($policy->estado === 'contratado')
                <span class="badge badge-contracted">Contratado</span>
            @else
                <span class="badge badge-quoted">Cotizado</span>
            @endif
        </div>
    </div>
</body>
</html>
