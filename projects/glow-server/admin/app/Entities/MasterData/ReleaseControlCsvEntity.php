<?php

namespace App\Entities\MasterData;

use App\Constants\SpreadSheetLabel;
use App\Utils\SpreadSheetSerialDate;
use Carbon\CarbonTimeZone;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReleaseControlCsvEntity
{
    private array $releaseKeyCsv;
    private string $gitRevision;

    // 有効なリリースキー
    private array $releaseKeys = [];

    // 全定義済みリリースキー（release_at昇順）
    public array $allReleaseKeys = [];

    public function __construct(array $releaseKeyCsv, string $gitRevision)
    {
        $this->releaseKeyCsv = $releaseKeyCsv;
        $this->gitRevision = $gitRevision;

        // 扱いやすいよう、内部でCSVを分解する
        $this->parseData();
    }

    public function getGitRevision(): string
    {
        return $this->gitRevision;
    }
    // 無効になっていないReleaseKeyリストを取得
    public function getAvailableReleaseKeys() : array
    {
        return array_keys($this->releaseKeys);
    }
    // 無効になっていないReleaseKeyのバージョン文字列リストを取得
    public function getAvailableVersions(): array
    {
        return array_map(fn ($key): string => $key.'_'.$this->gitRevision, array_keys($this->releaseKeys));
    }
    // 無効になっていないReleaseKeyのcsvデータを取得
    // ['release_key' => <リリースキー>, 'start_at' => <リリース日>, 'description' => <メモ>, 'status' => <配信中'apply'か未来'future'か>]
    public function getAvailableData() : array
    {
        return $this->releaseKeys;
    }
    // 指定されたリリースキーよりも未来に適用されるリリースキーのリストを取得
    public function getExcludeKeys(string $releaseKey): array
    {
        $searchKeys = array_keys($this->allReleaseKeys, $releaseKey);
        if (empty($searchKeys)) return $this->allReleaseKeys;
        $lastKey = end($searchKeys);

        $index = array_search($lastKey, array_keys($this->allReleaseKeys)) + 1;
        return array_slice($this->allReleaseKeys, $index);
    }

    // TODO: VQ依存を切り離す
    private function parseData(): void
    {
        // TODO: まとめたい
        $now = new DateTime();

        $releaseKeyIndex = null;
        $startAtIndex = null;
        $descriptionIndex = null;
        $applicableReleaseKey = [];
        $futureReleaseKeys = [];

        foreach($this->releaseKeyCsv as $i => $releaseControl)
        {
            // ヘッダ取得
            if ($i === 0)
            {
                $releaseKeyIndex = $this->findIndexBySnakeCase(SpreadSheetLabel::RELEASE_KEY_COLUMN, $releaseControl);
                $startAtIndex = $this->findIndexBySnakeCase(SpreadSheetLabel::START_AT_COLUMN, $releaseControl);
                $descriptionIndex = $this->findIndexBySnakeCase(SpreadSheetLabel::DESCRIPTION_COLUMN, $releaseControl);
                continue;
            }

            $releaseKey = $releaseControl[$releaseKeyIndex];
            if (is_numeric($releaseControl[$startAtIndex])) {
                $startAt = SpreadSheetSerialDate::convertSerialDateToDateTime($releaseControl[$startAtIndex], 'UTC');
            } else {
                $startAt = new Carbon($releaseControl[$startAtIndex], new CarbonTimeZone('UTC'));
            }
            $this->allReleaseKeys[$startAt->timestamp] = $releaseKey;
            $description = $releaseControl[$descriptionIndex];
            if ($startAt <= $now)
            {
                // 最後のreleaseKeyを特定
                if (!isset($applicableReleaseKey[$releaseKey]) || $applicableReleaseKey[$releaseKey] < $startAt)
                {
                    $applicableReleaseKey = [];
                    $applicableReleaseKey[$releaseKey] = [
                        SpreadSheetLabel::RELEASE_KEY_COLUMN => $releaseKey,
                        SpreadSheetLabel::START_AT_COLUMN => $startAt->format(SpreadSheetLabel::DATETIME_FORMAT_SPREADSHEET),
                        SpreadSheetLabel::DESCRIPTION_COLUMN => $description,
                        'status' => 'apply',
                    ];
                }
            } else {
                if (!isset($futureReleaseKeys[$releaseKey]) || $futureReleaseKeys[$releaseKey] < $startAt) {
                    $futureReleaseKeys[$releaseKey] = [
                        SpreadSheetLabel::RELEASE_KEY_COLUMN => $releaseKey,
                        SpreadSheetLabel::START_AT_COLUMN => $startAt->format(SpreadSheetLabel::DATETIME_FORMAT_SPREADSHEET),
                        SpreadSheetLabel::DESCRIPTION_COLUMN => $description,
                        'status' => 'future',
                    ];
                }
            }
        }
        ksort($this->allReleaseKeys);
        $this->releaseKeys = $applicableReleaseKey + $futureReleaseKeys;
    }

    private function findIndexBySnakeCase(string $needle, array $haystack): int | bool {
        $filteredArray = array_filter($haystack, fn ($v) => Str::snake($needle) === Str::snake($v));
        return !empty($filteredArray) ? key($filteredArray) : false;
    }
}
