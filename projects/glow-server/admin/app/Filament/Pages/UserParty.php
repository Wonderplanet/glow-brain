<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrParty;
use App\Models\Usr\UsrUnit;
use App\Services\AssetService;
use App\Tables\Columns\UsrPartyUnitInfoColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class UserParty extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-party';

    public string $currentTab = UserSearchTabs::PARTY->value;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = UsrParty::query()->where('usr_user_id', $this->userId)->orderBy('party_no');

        $usrUnitIds = $query->get()->map(function (UsrParty $usrParty) {
            return $usrParty->getUsrUnitIds();
        })->flatten()->unique();
        $usrUnits = UsrUnit::query()
            ->with('mst_unit.mst_unit_i18n')
            ->where('usr_user_id', $this->userId)
            ->whereIn('id', $usrUnitIds)
            ->get()
            ->keyBy('id');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('party_no')->label('パーティNO'),
                TextColumn::make('party_name')->label('パーティ名'),
                UsrPartyUnitInfoColumn::make('unit_info')->label('編成キャラ情報')
                    ->getStateUsing(
                        function ($record) use ($usrUnits) {
                            $units = [];
                            foreach ($record->getUsrUnitIds() as $usrUnitId) {
                                $usrUnit = $usrUnits->get($usrUnitId);
                                $mstUnit = $usrUnit->mst_unit;
                                $units[] = [
                                    'id' => $mstUnit->id,
                                    'name' => $mstUnit->mst_unit_i18n->name ?? '',
                                    'level' => $usrUnit->getLevel(),
                                    'rank' => $usrUnit->getRank(),
                                    'gradeLevel' => $usrUnit->getGradeLevel(),
                                    'assetPath' => $mstUnit->makeAssetPath(),
                                    'bgPath' => $mstUnit->makeBgPath(),
                                ];
                            }
                            // 未設定枠も表示するためにnullで埋める
                            return array_pad($units, 10, null);
                        }),
            ])
            ->filters([], FiltersLayout::AboveContent)
            ->deferFilters()
            ->actions([
                Action::make('edit')->label('編集')
                    ->button()
                    ->url(function (UsrParty $usrParty) {
                        return EditUserParty::getUrl([
                            'userId' => $this->userId,
                            'partyNo' => $usrParty->party_no,
                        ]);
                    })
                    ->visible(fn () => EditUserParty::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
