<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use Illuminate\Http\JsonResponse;

class DebugResponseFactory
{
    /**
     * @param array{product_list: array<array{product_sub_id: string, mst_store_product_id: string, product_id: string, paid_amount: int}>} $data
     * @return JsonResponse
     */
    public function createBillingProductListResponse(array $data): JsonResponse
    {
        return response()->json($data);
    }
}
