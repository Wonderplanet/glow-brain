<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Models\Adm\AdmBankF001;
use App\Models\Log\LogBank;
use App\Repositories\Adm\AdmBankF001Repository;
use App\Repositories\Usr\UsrUserRepository;
use App\Services\EnvironmentService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Bank KPI f001ログサービス
 */
class BankF001Service
{
    public function __construct(
        // Repository
        private AdmBankF001Repository $admBankF001Repository,
        private UsrUserRepository $usrUserRepository,
        // Service
        private BankService $bankService,
        private EnvironmentService $environmentService,
    ) {
    }

    /**
     * logBanksを元にBankF001を生成する
     *
     * @param string $env
     * @param Collection<LogBank> $logBanks
     * @param CarbonImmutable $now
     * @return Collection<AdmBankF001>
     */
    public function createLog(string $env, Collection $logBanks, CarbonImmutable $now): Collection
    {
        $applicationId = $this->environmentService->getApplicationId();
        $bnUserIds = $this->usrUserRepository->getBnUserIdsByUserIds(
            $logBanks->map(fn(LogBank $log) => $log->getUsrUserId())
        );
        $fluentdTag = $this->bankService->getFluentdTag($env, $applicationId, BankKPIFormatType::F001->value);
        $models = $logBanks->map(function (LogBank $log) use (
            $fluentdTag,
            $applicationId,
            $bnUserIds,
        ) {
            return $this->admBankF001Repository->createModel(
                $fluentdTag,
                BankKPIConstant::VERSION,
                $log->getEventId(),
                $log->getCreatedAt(),
                $applicationId,
                $log->getUsrUserId(),
                '', // 未使用
                $bnUserIds->get($log->getUsrUserId()) ?? '',
                '', // v3の場合は未使用
                '', // v3の場合は未使用
                '', // v3の場合は未使用
                $this->bankService->getPlatformIdByOsPlatformNum($log->getOsPlatform()),
                $log->getOsVersion(),
                $log->getPlatformUserId(),
                $log->getUserAgent(),
                $log->getUserFirstCreatedAt(),
                $log->getCountryCode(),
                $this->bankService->getFormattedOrDefaultAdId($log->getAdId())
            );
        })
        ->filter(fn($model) => $model !== null);
        $this->admBankF001Repository->bulkInsert($models, $now);
        return $models;
    }

    /**
     * BankF001のデータを整形する
     *
     * @param Collection<AdmBankF001> $admBankF001s
     * @return Collection<string>
     */
    public function formatDataRecords(
        Collection $admBankF001s
    ): Collection {
        return $admBankF001s->map(function (AdmBankF001 $admBankF001) {
            return implode("\t", [
                CarbonImmutable::parse($admBankF001->getEventTime())->format('Y-m-d\TH:i:s\Z') ?? '',
                $admBankF001->getFluentdTag(),
                json_encode([
                    'version' => $admBankF001->getVersion(),
                    'app_id' => $admBankF001->getAppId(),
                    'client_id' => $this->environmentService->getClientId(),
                    'client_secret' => $this->environmentService->getClientSecret(),
                    'event_id' => $admBankF001->getEventId(),
                    'event_time' => $admBankF001->getEventTime(),
                    'app_user_id' => $admBankF001->getAppUserId(),
                    'app_system_prefix' => $admBankF001->getAppSystemPrefix(),
                    'platform_user_id' => $admBankF001->getPlatformUserId(),
                    'user_id' => $admBankF001->getUserId(),
                    'person_id' => $admBankF001->getPersonId(),
                    'mbid' => $admBankF001->getMbid(),
                    'ktid' => $admBankF001->getKtid(),
                    'platform_id' => $admBankF001->getPlatformId(),
                    'platform_version' => $admBankF001->getPlatformVersion(),
                    'user_agent' => $admBankF001->getUserAgent(),
                    'created_time' => $admBankF001->getCreatedTime(),
                    'country_code' => $admBankF001->getCountryCode(),
                    'ad_id' => $admBankF001->getAdId(),
                ]),
            ]);
        });
    }
}
