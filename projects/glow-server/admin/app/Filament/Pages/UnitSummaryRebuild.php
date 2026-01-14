<?php

namespace App\Filament\Pages;
use App\Constants\Database;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Models\Usr\UsrUnit;
use App\Models\Usr\UsrUnitSummary;
use App\Traits\DatabaseTransactionTrait;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class UnitSummaryRebuild extends Page
{
    use Authorizable;
    use DatabaseTransactionTrait;
    
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static string $view = 'filament.pages.unit-summary-rebuild';
    protected static ?string $title = '図鑑ランク集計機能';
    protected static bool $shouldRegisterNavigation = true;

    public string $usrUserId = '';
    public ?array $unitSummaryData = [];

    protected $queryString = [
        'usrUserId',
    ];

    public function mount(): void
    {
        if ($this->usrUserId) {
            $this->loadUnitSummaryData();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('usrUserId')
                    ->label('ユーザーID')
                    ->required()
                    ->placeholder('ユーザーIDを入力してください')
                    ->afterStateUpdated(function () {
                        $this->unitSummaryData = [];
                    }),
                Actions::make([
                    Action::make('loadData')
                        ->label('図鑑ランク情報を読み込み')
                        ->color('primary')
                        ->action('loadUnitSummaryData'),
                    Action::make('rebuildSummary')
                        ->label('図鑑ランク情報を再構築')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('図鑑ランク情報を再構築しますか？')
                        ->modalDescription('既存の図鑑ランク情報は削除され、現在の所持ユニット情報から再計算されます。')
                        ->action('rebuildUnitSummary')
                        ->disabled(fn() => empty($this->usrUserId)),
                ]),
            ]);
    }

    public function loadUnitSummaryData(): void
    {
        if (empty($this->usrUserId)) {
            Notification::make()
                ->title('エラー')
                ->body('ユーザーIDを入力してください。')
                ->danger()
                ->send();
            return;
        }

        try {
            // 現在のユニットサマリーを取得
            $currentSummary = UsrUnitSummary::where('usr_user_id', $this->usrUserId)->first();

            // 所持ユニットを取得
            $usrUnits = UsrUnit::where('usr_user_id', $this->usrUserId)
                ->with(['mst_unit.mst_unit_i18n'])
                ->get();

            // ユニットごとの詳細計算
            $unitDetails = $usrUnits->map(function ($unit) {
                return [
                    'mst_unit_id' => $unit->mst_unit_id,
                    'unit_name' => $unit->mst_unit->mst_unit_i18n->name ?? 'Unknown',
                    'max_grade_level' => $unit->grade_level,
                    'max_level' => $unit->level,
                    'max_rank' => $unit->rank,
                    'total_count' => 1,
                ];
            });

            // 総グレード計算
            $calculatedTotalGrade = $usrUnits->sum('grade_level');
            $currentTotalGrade = $currentSummary ? $currentSummary->grade_level_total_count : 0;

            $this->unitSummaryData = [
                'unit_details' => $unitDetails->toArray(),
                'total_units' => $usrUnits->count(),
                'calculated_total_grade' => $calculatedTotalGrade,
                'current_total_grade' => $currentTotalGrade,
                'summary_exists' => $currentSummary !== null,
                'summary_updated_at' => $currentSummary ? $currentSummary->updated_at->format('Y-m-d H:i:s') : null,
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->title('エラー')
                ->body('図鑑ランク情報の読み込みに失敗しました: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function rebuildUnitSummary(): void
    {
        if (empty($this->usrUserId)) {
            Notification::make()
                ->title('エラー')
                ->body('ユーザーIDを入力してください。')
                ->danger()
                ->send();
            return;
        }

        try {
            $result = $this->rebuildUserUnitSummaries($this->usrUserId);

            Notification::make()
                ->title('再構築完了')
                ->body("ユーザー {$this->usrUserId} の図鑑ランク情報を再構築しました。（総ユニット数: {$result['total_units']}, 総グレード: {$result['total_grade']}）")
                ->success()
                ->send();

            // データを再読み込み
            $this->loadUnitSummaryData();

        } catch (\Exception $e) {
            Notification::make()
                ->title('エラー')
                ->body('図鑑ランク情報の再構築に失敗しました: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * ユーザーのユニットサマリーを再構築する
     */
    private function rebuildUserUnitSummaries(string $usrUserId): array
    {
        return $this->transaction(function () use ($usrUserId) {
            // ユーザーの所持ユニットを取得
            $usrUnits = UsrUnit::where('usr_user_id', $usrUserId)->get();

            if ($usrUnits->isEmpty()) {
                // 既存のサマリーを削除
                UsrUnitSummary::where('usr_user_id', $usrUserId)->delete();
                
                return [
                    'total_grade' => 0,
                    'total_units' => 0,
                ];
            }

            // 総グレードを計算
            $totalGrade = $this->calculateTotalGrade($usrUnits);

            // 既存のサマリーを確認
            $existingSummary = UsrUnitSummary::where('usr_user_id', $usrUserId)->first();
            
            if ($existingSummary) {
                // 既存の場合は更新のみ
                $existingSummary->update([
                    'grade_level_total_count' => $totalGrade,
                ]);
            } else {
                // 新規作成の場合のみidを指定
                UsrUnitSummary::create([
                    'id' => $usrUserId . '_summary',
                    'usr_user_id' => $usrUserId,
                    'grade_level_total_count' => $totalGrade,
                ]);
            }

            return [
                'total_grade' => $totalGrade,
                'total_units' => $usrUnits->count(),
            ];
        }, [Database::TIDB_CONNECTION]);
    }

    /**
     * ユニットの総グレードを計算する
     */
    private function calculateTotalGrade($usrUnits): int
    {
        // usr_unitsは一意なので直接sum可能
        return $usrUnits->sum('grade_level');
    }
}
