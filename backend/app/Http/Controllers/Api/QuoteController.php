<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractPolicyRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\PolicyCollection;
use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use App\Services\QuoteService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuoteController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly QuoteService $quotes)
    {
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $policy = $this->quotes->create($request->validated());

        return $this->successResponse(
            new PolicyResource($policy->load('insured')),
            'Cotización generada correctamente.',
            Response::HTTP_CREATED,
        );
    }

    public function index(Request $request): JsonResponse
    {
        $policies = Policy::query()
            ->with('insured')
            ->byStatus($request->query('estado'))
            ->byDestination($request->query('destino'))
            ->byIdentification($request->query('identificacion'))
            ->byClient($request->query('cliente'))
            ->byDateRange($request->query('desde'), $request->query('hasta'))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));

        return $this->successResponse(
            new PolicyCollection($policies),
            'Lista de cotizaciones obtenida correctamente.',
            Response::HTTP_OK,
            [
                'current_page' => $policies->currentPage(),
                'per_page' => $policies->perPage(),
                'total' => $policies->total(),
                'last_page' => $policies->lastPage(),
            ],
        );
    }

    public function show(Policy $policy): JsonResponse
    {
        return $this->successResponse(
            new PolicyResource($policy->load('insured')),
            'Detalle de la cotización obtenido correctamente.',
        );
    }

    public function contract(Policy $policy, ContractPolicyRequest $request): JsonResponse
    {
        $policy = $this->quotes->contract($policy);

        return $this->successResponse(
            new PolicyResource($policy->load('insured')),
            'Seguro contratado correctamente.',
        );
    }

    public function pdf(Policy $policy): Response
    {
        $pdf = Pdf::loadView('pdf.quote', ['policy' => $policy->load('insured')]);

        return $pdf->download("cotizacion-{$policy->id}.pdf");
    }
}
