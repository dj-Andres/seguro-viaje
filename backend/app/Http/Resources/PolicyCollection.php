<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PolicyCollection extends ResourceCollection
{
    /**
     * @return array<int, mixed>
     */
    public function toArray(Request $request): array
    {
        return PolicyResource::collection($this->collection)->resolve($request);
    }
}
