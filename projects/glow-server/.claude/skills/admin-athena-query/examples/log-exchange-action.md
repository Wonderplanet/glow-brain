# LogExchangeAction 実装例

## 目次

1. [ファイル構成](#ファイル構成)
2. [Model実装](#model実装)
3. [Filamentページ実装](#filamentページ実装)
4. [Bladeテンプレート](#bladeテンプレート)
5. [AthenaテーブルSQL（develop環境）](#athenaテーブルsqldevelop環境)
6. [参考PR](#参考pr)

---

交換所ログ（log_exchange_actions）のAthena対応実装例です。

## ファイル構成

```
admin/
├── app/
│   ├── Models/Log/
│   │   └── LogExchangeAction.php       # Athena対応モデル
│   └── Filament/Pages/
│       └── LogExchangeActionPage.php   # ログ表示ページ
├── resources/views/filament/pages/
│   └── log-exchange-action-page.blade.php
└── database/athena_tables/
    ├── develop/glow_develop_user_action_logs/
    │   └── log_exchange_actions.sql
    └── prod/glow_prod_user_action_logs/
        └── log_exchange_actions.sql
```

## Model実装

```php
<?php
// admin/app/Models/Log/LogExchangeAction.php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Exchange\Models\LogExchangeAction as BaseLogExchangeAction;
use App\Dtos\RewardDto;
use App\Entities\Reward;
use App\Models\Mst\MstExchangeLineup;
use App\Traits\AthenaModelTrait;
use Illuminate\Support\Collection;

class LogExchangeAction extends BaseLogExchangeAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    private ?Collection $rewardsEntitiesCache = null;

    public function mst_exchange_lineup()
    {
        return $this->hasOne(MstExchangeLineup::class, 'id', 'mst_exchange_lineup_id');
    }

    private function getRewardsEntities(): Collection
    {
        if ($this->rewardsEntitiesCache !== null) {
            return $this->rewardsEntitiesCache;
        }

        $rewardsArray = $this->rewards ?? [];
        $result = collect();
        foreach ($rewardsArray as $rewardArray) {
            $result->push(Reward::createByArray($rewardArray));
        }

        $this->rewardsEntitiesCache = $result;
        return $this->rewardsEntitiesCache;
    }

    public function getRewardsDtos(): Collection
    {
        $result = collect();
        foreach ($this->getRewardsEntities() as $reward) {
            $result->push(
                new RewardDto(
                    $reward->getId(),
                    $reward->getType(),
                    $reward->getResourceId(),
                    $reward->getAmount()
                )
            );
        }
        return $result;
    }

    public function getBeforeRewardsDtos(): Collection
    {
        $result = collect();
        $hasConverted = false;

        foreach ($this->getRewardsEntities() as $reward) {
            if ($reward->isConverted()) {
                $hasConverted = true;
            }
            $originalReward = $reward->getOriginalRewardData();
            $result->push(
                new RewardDto(
                    $reward->getId(),
                    $originalReward->getType(),
                    $originalReward->getResourceId(),
                    $originalReward->getAmount()
                )
            );
        }

        return $hasConverted ? $result : collect();
    }

    public function getCostsDtos(): Collection
    {
        $result = collect();
        $costsArray = $this->costs ?? [];

        foreach ($costsArray as $index => $cost) {
            $result->push(
                new RewardDto(
                    $this->id . '_cost_' . $index,
                    $cost['cost_type'] ?? '',
                    $cost['cost_id'] ?? null,
                    $cost['cost_amount'] ?? 0
                )
            );
        }

        return $result;
    }
}
```

## Filamentページ実装

```php
<?php
// admin/app/Filament/Pages/LogExchangeActionPage.php

namespace App\Filament\Pages;

use App\Constants\LogTablePageConstants;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogExchangeAction;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\RewardInfoGetTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogExchangeActionPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.log-exchange-action-page';
    public string $currentTab = UserSearchTabs::LOG_EXCHANGE->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = LogExchangeAction::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('交換日時')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_id')
                    ->label('交換所ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_lineup_id')
                    ->label('交換ラインナップID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trade_count')
                    ->label('交換回数')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('cost_info')
                    ->label('コスト情報')
                    ->getStateUsing(
                        fn($record) => $this->getRewardInfos($record->getCostsDtos())
                    ),
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        fn($record) => $this->getRewardInfos($record->getRewardsDtos())
                    ),
                RewardInfoColumn::make('before_reward_info')
                    ->label('報酬情報(変換前)')
                    ->getStateUsing(
                        fn($record) => $this->getRewardInfos($record->getBeforeRewardsDtos())
                    ),
                TextColumn::make('nginx_request_id')
                    ->label('Nginx Request ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_id')
                    ->label('Request ID')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters([
                        LogTablePageConstants::CREATED_AT_RANGE,
                        LogTablePageConstants::NGINX_REQUEST_ID
                    ]),
                    [
                        Filter::make('mst_exchange_lineup_id')
                            ->form([
                                TextInput::make('mst_exchange_lineup_id')
                                    ->label('交換ラインナップID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['mst_exchange_lineup_id'])) {
                                    return $query;
                                }
                                return $query->where(
                                    'mst_exchange_lineup_id',
                                    'like',
                                    "{$data['mst_exchange_lineup_id']}%"
                                );
                            }),
                    ]
                ),
                FiltersLayout::AboveContent
            )
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_exchange')
            ]);
    }
}
```

## Bladeテンプレート

```php
{{-- resources/views/filament/pages/log-exchange-action-page.blade.php --}}
<x-filament-panels::page>
    {{ $this->table }}
</x-filament-panels::page>
```

## AthenaテーブルSQL（develop環境）

```sql
-- log_exchange_actions
CREATE EXTERNAL TABLE `glow_develop_user_action_logs`.`log_exchange_actions` (
    `id` string COMMENT '',
    `usr_user_id` string COMMENT 'usr_users.id',
    `nginx_request_id` string COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
    `request_id` string COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
    `logging_no` int COMMENT 'APIリクエスト中でのログの順番',
    `mst_exchange_lineup_id` string COMMENT 'mst_exchange_lineups.id',
    `costs` string COMMENT '支払ったコスト情報',
    `rewards` string COMMENT '獲得した報酬情報',
    `trade_count` int COMMENT '交換個数',
    `created_at` string COMMENT '',
    `updated_at` string COMMENT ''
)
PARTITIONED BY (
    `dt` string
)
ROW FORMAT SERDE 'org.apache.hadoop.hive.serde2.OpenCSVSerde'
  WITH SERDEPROPERTIES (
    "separatorChar" = ",",
    'quoteChar' = '"',
    "serialization.null.format" = "\\N"
  )
STORED AS INPUTFORMAT
  'org.apache.hadoop.mapred.TextInputFormat'
OUTPUTFORMAT
  'org.apache.hadoop.hive.ql.io.HiveIgnoreKeyTextOutputFormat'
LOCATION
  's3://glow-develop-datalake/raw/tidb/log_exchange_actions'
TBLPROPERTIES (
  'classification'='csv',
  'skip.header.line.count'='1',
  'projection.enabled'='true',
  'projection.dt.type' = 'date',
  'projection.dt.range' = '2025/07/01,NOW',
  'projection.dt.format' = 'yyyy/MM/dd',
  'projection.dt.interval' = '1',
  'projection.dt.interval.unit' = 'DAYS',
  'storage.location.template' = 's3://glow-develop-datalake/raw/tidb/log_exchange_actions/${dt}/'
);
```

## 参考PR

- [#2021 交換所管理画面を追加](https://github.com/Wonderplanet/glow-server/pull/2021)
