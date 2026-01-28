<?php
namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstShopPass;
use App\Models\Usr\UsrShopPass;
use App\Tables\Columns\MstIdColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserShopPass extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.user-shop-pass';

    public string $currentTab = UserSearchTabs::SHOP_PASS->value;

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => UserSearchTabs::SHOP_PASS->value,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = MstShopPass::query()
            ->with([
                'mst_shop_pass_i18n',
                'mst_shop_pass_rewards',
                'opr_product',
            ]);

        $usrShopPasses = UsrShopPass::query()
            ->where('usr_user_id', $this->userId)
            ->get()
            ->keyBy(function (UsrShopPass $usrShopPass) {
                return $usrShopPass->mst_shop_pass_id;
            });

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopPass $mstShopPass) use ($usrShopPasses) {
                            $usrShopPass = $usrShopPasses->get($mstShopPass->id);
                            return $usrShopPass?->id ?? 'レコードなし';
                        }
                    ),
                MstIdColumn::make('mst_shop_pass_info')
                    ->label('パス情報')
                    ->searchable()
                    ->getMstIdUsing(function (MstShopPass $model) {
                        return $model->id;
                    })
                    ->getMstDataNameUsing(function (MstShopPass $model) {
                        return $model->mst_shop_pass_i18n->name;
                    })
                    ->getMstDetailPageUrlUsing(function (MstShopPass $model) {
                        return MstShopPassDetail::getUrl(['mstShopPassId' => $model->id]);
                    }),
                TextColumn::make('daily_reward_received_count')
                    ->label('毎日報酬受け取り回数')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopPass $mstShopPass) use ($usrShopPasses) {
                            $usrShopPass = $usrShopPasses->get($mstShopPass->id);
                            return $usrShopPass?->daily_reward_received_count ?? 0;
                        }
                    ),
                TextColumn::make('daily_latest_received_at')
                    ->label('毎日報酬受け取り日時')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopPass $mstShopPass) use ($usrShopPasses) {
                            $usrShopPass = $usrShopPasses->get($mstShopPass->id);
                            return $usrShopPass?->daily_latest_received_at;
                        }
                    ),
                TextColumn::make('start_at')
                    ->label('開始日時')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopPass $mstShopPass) use ($usrShopPasses) {
                            $usrShopPass = $usrShopPasses->get($mstShopPass->id);
                            return $usrShopPass?->start_at;
                        }
                    ),
                TextColumn::make('end_at')
                    ->label('終了日時')
                    ->searchable()
                    ->getStateUsing(
                        function (MstShopPass $mstShopPass) use ($usrShopPasses) {
                            $usrShopPass = $usrShopPasses->get($mstShopPass->id);
                            return $usrShopPass?->end_at;
                        }
                    ),
            ])
            ->filters([
                SelectFilter::make('duration')
                    ->form([
                        DatePicker::make('datetime')
                                ->label('有効日時'),
                    ])
                    ->label('有効日時')
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['datetime'])) {
                            return $query;
                        }
                        $mstShopPassIds = UsrShopPass::query()
                            ->where('usr_user_id', $this->userId)
                            ->where('start_at', '<=', $data['datetime'])
                            ->where('end_at', '>=', $data['datetime'])
                            ->pluck('mst_shop_pass_id');
                        return $query->whereIn('id', $mstShopPassIds);
                    }),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('edit')
                    ->label('編集')
                    ->button()
                    ->url(function (MstShopPass $record) {
                        return EditUserShopPass::getUrl([
                            'userId' => $this->userId,
                            'mstShopPassId' => $record->id,
                        ]);
                    })
                    ->visible(fn () => EditUserShopPass::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
