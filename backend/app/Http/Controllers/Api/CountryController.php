<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CountryService $countries)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $countries = $this->countries->list();

        $perPage = max(1, min(100, $request->integer('per_page', 10)));
        $page = max(1, $request->integer('page', 1));

        $paginator = new LengthAwarePaginator(
            $countries->forPage($page, $perPage)->values(),
            $countries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return $this->successResponse(
            $paginator->items(),
            'Lista de países obtenida correctamente.',
            Response::HTTP_OK,
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        );
    }
}
