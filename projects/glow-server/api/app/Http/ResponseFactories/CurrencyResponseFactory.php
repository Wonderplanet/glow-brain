<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use Illuminate\Http\JsonResponse;

class CurrencyResponseFactory
{
    use CurrencySummaryResponderTrait;

    /**
     *
     * @param string $bliingPlatform
     * @param array{currency_summary: \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity} $data
     * @return JsonResponse
     */
    public function createInfoSummaryResponse(string $bliingPlatform, array $data): JsonResponse
    {
        $result = [
            'currency_summary' => $this->createCurrencySummaryResponse($bliingPlatform, $data['currency_summary'])
        ];

        return response()->json($result);
    }

    /**
     *
     * @param string $bliingPlatform
     * @param array{currency_summary: \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity} $data
     * @return JsonResponse
     */
    public function createConsumeResponse(string $bliingPlatform, array $data): JsonResponse
    {
        $result = [
            'currency_summary' => $this->createCurrencySummaryResponse($bliingPlatform, $data['currency_summary'])
        ];

        return response()->json($result);
    }

    /**
     *
     * @param array{currency_paids?: array<\WonderPlanet\Domain\Currency\Entities\UsrCurrencyPaidEntity>} $data
     * @return JsonResponse
     */
    public function createInfoPaidResponse(array $data): JsonResponse
    {
        $currencyPaids = [];

        if (isset($data['currency_paids'])) {
            foreach ($data['currency_paids'] as $currencyPaid) {
                $currencyPaids[] = [
                    "id" => $currencyPaid->id,
                    "seq_no" => $currencyPaid->seq_no,
                    "user_id" => $currencyPaid->usr_user_id,
                    "left_amount" => $currencyPaid->left_amount,
                    "purchase_price" => $currencyPaid->purchase_price,
                    "price_per_amount" => $currencyPaid->price_per_amount,
                    "currency_code" => $currencyPaid->currency_code,
                    "receipt_unique_id" => $currencyPaid->receipt_unique_id,
                    "is_sandbox" => $currencyPaid->is_sandbox,
                    "os_platform" => $currencyPaid->os_platform,
                    "billing_platform" => $currencyPaid->billing_platform,
                    "created_at" => $currencyPaid->created_at->format('Y-m-d H:i:s'),
                    "updated_at" => $currencyPaid->updated_at->format('Y-m-d H:i:s'),
                ];
            }
        }

        $result = [
            'currency_paids' => $currencyPaids
        ];

        return response()->json($result);
    }
}
