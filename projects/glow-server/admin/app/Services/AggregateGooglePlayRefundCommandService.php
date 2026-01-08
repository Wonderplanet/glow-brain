<?php

declare(strict_types=1);

namespace App\Services;
use App\Constants\Database;
use App\Domain\GooglePlay\Repositories\LogGooglePlayRefundRepository;
use App\Entities\Clock;
use App\Operators\S3Operator;
use App\Traits\DatabaseTransactionTrait;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;

readonly class AggregateGooglePlayRefundCommandService
{
    use DatabaseTransactionTrait;

    private const TIMEZONE = 'Asia/Tokyo';

    public function __construct(
        // Services
        private EnvironmentService $environmentService,
        // Repositories
        private LogGooglePlayRefundRepository $logGooglePlayRefundRepository,
        // Common
        private S3Operator $s3Operator,
        private Clock $clock,
    ) {
    }

    public function exec(): void
    {
        // s3からファイルを取得
        $refundCsv = $this->fetchVoidedPurchaseCsvFromS3();

        // CSVが取得できなかった場合は処理を終了
        if ($refundCsv === null) {
            return;
        }

        // CSVを解析
        $refundList = $this->parseRefundCsv($refundCsv);

        $this->transaction(
            function () use ($refundList) {
                // GooglePlayの返金情報を生成
                $this->generateGooglePlayRefunds($refundList);
            },
            [Database::TIDB_CONNECTION]
        );
    }

    /**
     * BNE S3からVoided Purchase APIのCSVを取得
     * @return string|null ファイルが存在しない場合はnullを返す
     */
    private function fetchVoidedPurchaseCsvFromS3(): ?string
    {
        $csvPath = $this->generateVoidedPurchaseCsvPath();
        $bucket = $this->environmentService->getBneVoidedPurchaseS3Bucket();

        $result = $this->s3Operator->getFileWithConfig(
            [
                'region' => $this->environmentService->getBneVoidedPurchaseS3Region(),
                'key' => $this->environmentService->getBneVoidedPurchaseAwsAccessKey(),
                'secret' => $this->environmentService->getBneVoidedPurchaseAwsSecretAccessKey(),
            ],
            $bucket,
            $csvPath
        );

        if ($result === null) {
            Log::info('Voided Purchase CSVファイルが存在しませんでした。処理を終了します。', [
                'bucket' => $bucket,
                'path' => $csvPath,
            ]);
            return null;
        }

        return $result['Body']->getContents();
    }

    /**
     * Voided Purchase APIデータのCSVのパスを生成
     * @return string
     */
    private function generateVoidedPurchaseCsvPath(): string
    {
        // 設置されるCSVが2日前のものなので、その日付を取得
        $targetDate = CarbonImmutable::now()->setTimezone(self::TIMEZONE)->subDays(2);
        $yearMonth = $targetDate->format('Ym');
        $day = $targetDate->format('d');
        return StringUtil::joinPath(
            $this->environmentService->getBneTitleId(),
            'Google',
            $this->environmentService->getPackageName(),
            'voidedpurchace',
            $yearMonth,
            "android_{$day}_vp.csv",
        );
    }

    /**
     * Voided Purchase APIデータのCSVを解析してpurchase_tokenとvoided_time_millisを取得
     * @param string $csv
     * @return Collection
     */
    private function parseRefundCsv(string $csv): Collection
    {
        // CSVからpurchase_tokenとvoided_time_millisを取得
        $refundList = collect();
        $lines = explode("\n", $csv); // 改行で分割
        foreach ($lines as $index => $line) {
            // 空行をスキップ
            if (trim($line) === '') {
                continue;
            }

            if ($index === 0) {
                // ヘッダ行をスキップ
                continue;
            }

            // CSVの1行を配列として解析
            $row = str_getcsv($line);

            // csvのインデックス
            // 0: title_id(未使用)
            // 1: title_name(未使用)
            // 2: pf_unique_code(未使用)
            // 3: purchase_token
            // 4: purchase_time_milles(未使用)
            // 5: voided_time_millis
            $refundedAt = CarbonImmutable::createFromTimestampMs($row[5])->setTimezone(self::TIMEZONE);
            $refundList->put($row[3], $refundedAt);
        }
        return $refundList;
    }

    /**
     * GooglePlayの返金情報を生成
     * @param Collection $refundList
     * @throws BindingResolutionException|\Throwable
     */
    private function generateGooglePlayRefunds(Collection $refundList): void
    {
        $now = $this->clock->now();
        $purchaseTokens = $refundList->keys();

        UsrStoreProductHistory::query()
            ->whereIn('receipt_purchase_token', $purchaseTokens)
            ->chunk(10000, function ($usrStoreProductHistories) use ($refundList, $now) {
                $logGooglePlayRefunds = collect();
                foreach ($usrStoreProductHistories as $usrStoreProductHistory) {
                    /** @var UsrStoreProductHistory $usrStoreProductHistory */
                    $transactionId = $usrStoreProductHistory->receipt_unique_id;
                    $purchaseToken = $usrStoreProductHistory->receipt_purchase_token;
                    $refundedAt = $refundList->get($purchaseToken);
                    if ($refundedAt === null) {
                        continue;
                    }

                    // DBデータと照合してLogGooglePlayRefundモデルを作る
                    /** @var CarbonImmutable $refundedAt */
                    $logGooglePlayRefund = $this->logGooglePlayRefundRepository->create(
                        $transactionId,
                        (int)$usrStoreProductHistory->purchase_price,
                        $refundedAt->format(Clock::DATETIME_FORMAT),
                        $purchaseToken
                    );
                    $logGooglePlayRefunds->push($logGooglePlayRefund);
                }

                // DBに保存
                $this->logGooglePlayRefundRepository->bulkCreate($logGooglePlayRefunds, $now);
            });
    }
}
