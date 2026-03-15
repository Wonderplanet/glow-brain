<?php

declare(strict_types=1);

namespace App\Traits;

use App\Dtos\LogTriggerDto;
use App\Services\LogTrigger\LogTriggerInfoGetService;
use App\Utils\StringUtil;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

trait LogTriggerInfoGetTrait
{
    /**
     * @param Collection<LogTriggerDto> $logTriggerDtos
     */
    public function createLogTriggers(Collection $logTriggerDtos): Collection
    {
        /** @var LogTriggerInfoGetService $service */
        $service = app(LogTriggerInfoGetService::class);
        return $service->createLogTriggers($logTriggerDtos);
    }

    /**
     * テーブル表示のページネーションで取得されるレコードデータに、LogTrigger情報を追加する。
     *
     * ページネートで取得したレコードのLogTrigger情報だけを効率的に取得できるようにするため。
     *
     * @param string $logTriggerKey モデルクラスでLogTrigger情報を取得する際の属性名を指定する
     * @param string $logTriggerInfoKey テーブル表示時にLogTrigger情報を識別するための属性名を指定する
     */
    public function addLogTriggerInfoToPaginatedRecords(
        Paginator $paginator,
        string $logTriggerKey = 'logTrigger',
        string $logTriggerInfoKey = 'log_trigger_info',
    ): void {
        $records = $paginator->getCollection();

        $logTriggerDtos = collect();
        foreach ($records as $record) {
            if (StringUtil::isNotSpecified($record->trigger_value)) {
                continue;
            }
            $logTriggerDto = $record->{$logTriggerKey};
            if ($logTriggerDto === null) {
                continue;
            }
            $logTriggerDtos->push($logTriggerDto);
        }

        $logTriggers = $this->createLogTriggers($logTriggerDtos);

        $records = $records->map(function ($record) use ($logTriggers, $logTriggerInfoKey) {
            $record->{$logTriggerInfoKey} = $logTriggers->get($record->trigger_value);
            return $record;
        });

        $paginator->setCollection($records);
    }
}
