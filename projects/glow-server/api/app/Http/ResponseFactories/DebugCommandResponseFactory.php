<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use Illuminate\Http\JsonResponse;

class DebugCommandResponseFactory
{
    /**
     * @param array<string> $commandLists
     * @return JsonResponse
     */
    public function createDebugCommandListData(array $commandLists): JsonResponse
    {
        return response()->json($commandLists);
    }
}
