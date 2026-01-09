<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\LogTablePageConstants;
use App\Constants\SystemConstants;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * ユーザーログ閲覧ページで共通的に使用するテーブルフィルターのトレイト
 */
trait UserLogTableFilterTrait
{
    use NotificationTrait;

    /**
     * 日付範囲フィルターの通知を既に表示したかどうか（同一リクエスト内での重複表示を防ぐ）
     */
    private static bool $dateRangeNotificationShown = false;

    /**
     * 日付範囲制限の通知を既に表示したかどうか（同一リクエスト内での重複表示を防ぐ）
     */
    private static bool $dateRangeLimitNotificationShown = false;

    /**
     * 指定されたフィルター名に基づいて共通フィルターを取得する
     *
     * @param array $filterNames フィルター名の配列 デフォルト: LogTablePageConstants::DEFAULT_COMMON_FILTERS
     * @return array
     */
    protected function getCommonLogFilters(array $filterNames = LogTablePageConstants::DEFAULT_COMMON_FILTERS): array
    {
        $availableFilters = [
            LogTablePageConstants::CREATED_AT_RANGE => $this->getCreatedAtRangeFilter(),
            LogTablePageConstants::NGINX_REQUEST_ID => $this->getNginxRequestIdFilter(),
        ];

        $filters = [];
        foreach ($filterNames as $filterName) {
            if (isset($availableFilters[$filterName])) {
                $filters[] = $availableFilters[$filterName];
            }
        }

        return $filters;
    }

    /**
     * 日付範囲が制限を超えていないかチェックする
     *
     * @param CarbonImmutable $startAt 開始日時
     * @param CarbonImmutable $endAt 終了日時
     * @return bool 制限を超えている場合はtrue
     */
    private function isDateRangeExceedingLimit(CarbonImmutable $startAt, CarbonImmutable $endAt): bool
    {
        $dateRangeLimitDays = LogTablePageConstants::DATE_RANGE_LIMIT_DAYS;
        return $startAt->diffInDays($endAt, absolute: true) >= $dateRangeLimitDays;
    }

    /**
     * 日付範囲未指定の通知を表示する（重複表示を防ぐ）
     */
    private function notifyDateRangeRequired(): void
    {
        if (!self::$dateRangeNotificationShown) {
            $this->sendDangerNotification(
                '日付範囲を指定してください。',
                'データベースの負荷軽減のため、日付範囲の指定を必須としております。',
            );
            self::$dateRangeNotificationShown = true;
        }
    }

    /**
     * 日付範囲制限超過の通知を表示する（重複表示を防ぐ）
     */
    private function notifyDateRangeLimitExceeded(): void
    {
        if (!self::$dateRangeLimitNotificationShown) {
            $dateRangeLimitDays = LogTablePageConstants::DATE_RANGE_LIMIT_DAYS;
            $this->sendDangerNotification(
                sprintf('日付範囲は%d日以内でお願いします。', $dateRangeLimitDays),
                'データベースの負荷軽減のため、日付範囲を制限しております。',
            );
            self::$dateRangeLimitNotificationShown = true;
        }
    }

    /**
     * 日付範囲フィルターを取得する
     */
    private function getCreatedAtRangeFilter(): Filter
    {
        return Filter::make(LogTablePageConstants::CREATED_AT_RANGE)
            ->form([
                DateTimePicker::make(LogTablePageConstants::CREATED_AT_RANGE_START_AT)
                    ->seconds(false)
                    ->required()
                    ->label('開始日時(JST)'),
                DateTimePicker::make(LogTablePageConstants::CREATED_AT_RANGE_END_AT)
                    ->seconds(false)
                    ->required()
                    ->label('終了日時(JST)'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                $isBlankStartAt = blank($data[LogTablePageConstants::CREATED_AT_RANGE_START_AT]);
                $isBlankEndAt = blank($data[LogTablePageConstants::CREATED_AT_RANGE_END_AT]);

                // ページ初回表示時を含む、両方未指定の場合は何も返さない
                if ($isBlankStartAt && $isBlankEndAt) {
                    $query->whereRaw('1 = 0');
                    return $query;
                }

                if (!$isBlankStartAt && !$isBlankEndAt) {
                    // 日付範囲の指定がある場合
                    $startAt = CarbonImmutable::parse($data[LogTablePageConstants::CREATED_AT_RANGE_START_AT], SystemConstants::VIEW_TIMEZONE);
                    $endAt = CarbonImmutable::parse($data[LogTablePageConstants::CREATED_AT_RANGE_END_AT], SystemConstants::VIEW_TIMEZONE);

                    // 日付範囲が制限を超えている場合は、無効なクエリにして通知を表示
                    if ($this->isDateRangeExceedingLimit($startAt, $endAt)) {
                        $query->whereRaw('1 = 0');
                        $this->notifyDateRangeLimitExceeded();
                        return $query;
                    }

                    // 有効な日付範囲の場合はフィルターを適用
                    $query->whereBetween('created_at', [$startAt, $endAt]);
                } else {
                    // ログデータは非常に多いため、負荷軽減とコスト増加防止のため、
                    // 日付範囲が指定されていない場合は何も返さないようにする
                    $query->whereRaw('1 = 0');
                    $this->notifyDateRangeRequired();
                }

                return $query;
            });
    }

    /**
     * APIリクエストIDフィルターを取得する
     */
    private function getNginxRequestIdFilter(): Filter
    {
        return Filter::make(LogTablePageConstants::NGINX_REQUEST_ID)
            ->form([
                TextInput::make('nginx_request_id')
                    ->label('APIリクエストID')
            ])
            ->query(function (Builder $query, array $data): Builder {
                if (blank($data['nginx_request_id'])) {
                    return $query;
                }
                return $query->where('nginx_request_id', 'like', "{$data['nginx_request_id']}%");
            });
    }
}
