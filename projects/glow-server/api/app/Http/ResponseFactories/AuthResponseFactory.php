<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use Illuminate\Http\JsonResponse;

class AuthResponseFactory
{
    use CurrencySummaryResponderTrait;

    /**
     *
     * @param string $billingPlatform
     * @param array{id_token: string, currency_summary: \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity} $data
     * @return JsonResponse
     */
    public function createSignUpResponse(string $billingPlatform, array $data): JsonResponse
    {

        $result = [
            'id_token' => $data['id_token'],
            'currency_summary' => $this->createCurrencySummaryResponse($billingPlatform, $data['currency_summary'])
        ];

        return response()->json($result);
    }
}
