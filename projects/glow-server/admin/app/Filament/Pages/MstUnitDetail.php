<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Constants\NavigationGroups;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstUnitResource;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstConfig;
use App\Models\Mst\MstItem;
use App\Models\Mst\MstUnit;
use App\Models\Mst\MstUnitGradeCoefficient;
use App\Models\Mst\MstUnitGradeUp;
use App\Models\Mst\MstUnitLevelUp;
use App\Models\Mst\MstUnitRankUp;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class MstUnitDetail extends MstDetailBasePage
{

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.unit-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $title = 'キャラ詳細';

    public string $mstUnitId = '';

    protected $queryString = [
        'mstUnitId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstUnitResource::class;
    }

    protected function getMstModelByQuery(): ?MstUnit
    {
        return MstUnit::query()
            ->where('id', $this->mstUnitId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_units.id %s', $this->mstUnitId);
    }

    protected function getSubTitle(): string
    {
        $mstUnit = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstUnit->id,
            $mstUnit->mst_unit_i18n->name ?? '',
        );
    }

    public function infoList(): Infolist
    {
        $mstUnit = $this->getMstModel();

        $mstItem = MstItem::query()
            ->where('id', $mstUnit->fragment_mst_item_id)
            ->first();
        $mstItemI18n = $mstItem->mst_item_i18n;
        $mstSeries = $mstUnit->mst_series;

        $state = [
            'id'                        => $mstUnit->id,
            'name'                      => $mstUnit->mst_unit_i18n->name,
            'fragment'                  => sprintf('[%s] %s', $mstItem->id, $mstItemI18n->name),
            'unit_label'                => $mstUnit->unit_label,
            'image_id'                  => $mstUnit->image_id,
            'asset_key'                 => $mstUnit->asset_key,
            'rarity'                    => $mstUnit->rarity,
            'sort_order'                => $mstUnit->sort_order,
            'series_asset_key'          => $mstUnit->series_asset_key,
            'mst_series_id'             => $mstSeries ? '[' . $mstSeries->id . '] ' . $mstSeries->mst_series_i18n->name : '',
            'release_key'               => $mstUnit->release_key,
            'asset_image'               => $mstUnit,
        ];
        $fieldset = Fieldset::make('基本情報')
            ->schema([
                TextEntry::make('id')->label('キャラID'),
                TextEntry::make('name')->label('キャラ名称'),
                TextEntry::make('fragment')->label('かけら'),
                TextEntry::make('unit_label')->label('ユニットラベル'),
                TextEntry::make('image_id')->label('イメージID'),
                TextEntry::make('asset_key')->label('アセットキー'),
                TextEntry::make('rarity')->label('レアリティ'),
                TextEntry::make('sort_order')->label('表示順'),
                TextEntry::make('series_asset_key')->label('シリーズアセットキー'),
                TextEntry::make('mst_series_id')->label('作品ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                AssetImageEntry::make('asset_image')->label('キャラ画像'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function battleStatusList(): InfoList
    {
        $mstUnit = $this->getMstModel();

        $state = [
            'role_type'                 => $mstUnit->role_type,
            'attack_range_type'         => $mstUnit->attack_range_type,
            'summon_cost'               => $mstUnit->summon_cost,
            'summon_cool_time'          => $mstUnit->summon_cool_time,
            'min_hp'                    => $mstUnit->min_hp,
            'max_hp'                    => $mstUnit->max_hp,
            'damage_knock_back_count'   => $mstUnit->damage_knock_back_count,
            'move_speed'                => $mstUnit->move_speed,
            'well_distance'             => $mstUnit->well_distance,
            'min_attack_power'          => $mstUnit->min_attack_power,
            'max_attack_power'          => $mstUnit->max_attack_power,
            'attack_combo_cycle'        => $mstUnit->attack_combo_cycle,
            'mst_unit_ability_id1'      => $mstUnit->mst_unit_ability_id1,
            'bounding_range_front'      => $mstUnit->bounding_range_front,
            'bounding_range_back'       => $mstUnit->bounding_range_back,
        ];
        $fieldset = Fieldset::make('バトルステータス')
            ->schema([
                TextEntry::make('role_type')->label('ロール'),
                TextEntry::make('attack_range_type')->label('射程'),
                TextEntry::make('summon_cost')->label('召喚コスト'),
                TextEntry::make('summon_cool_time')->label('召喚クールタイム'),
                TextEntry::make('min_hp')->label('最小HP'),
                TextEntry::make('max_hp')->label('最大HP'),
                TextEntry::make('damage_knock_back_count')->label('ダメージノックバックカウント'),
                TextEntry::make('move_speed')->label('移動速度'),
                TextEntry::make('well_distance')->label('well_distance'),
                TextEntry::make('min_attack_power')->label('最小攻撃力'),
                TextEntry::make('max_attack_power')->label('最大攻撃力'),
                TextEntry::make('attack_combo_cycle')->label('攻撃コンボサイクル'),
                TextEntry::make('mst_unit_ability_id1')->label('mst_unit_ability_id1'),
                TextEntry::make('bounding_range_front')->label('bounding_range_front'),
                TextEntry::make('bounding_range_back')->label('bounding_range_back'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function getLevelUpStarusesList(): array
    {
        $mstUnit = $this->getMstModel();

        $mstUnitLevelUp = MstUnitLevelUp::query()
            ->where('unit_label', $mstUnit->unit_label)
            ->get();

        $maxLevel = MstUnitLevelUp::query()->where('unit_label', $mstUnit->unit_label)->max('level');

        $levelStatuses = [];
        $increasedHp = null;
        $increasedAttackPoint = null;
        foreach ($mstUnitLevelUp as $value) {
            $levelUpHp = floor($mstUnit->min_hp + ($mstUnit->max_hp - $mstUnit->min_hp) * (($value->level -1)/($maxLevel -1)));
            $levelUpAttackPoint = floor($mstUnit->min_attack_power + ($mstUnit->max_attack_power - $mstUnit->min_attack_power) * (($value->level -1)/($maxLevel -1)));

            if ($value->level != 1) {
                $increasedHp = ' (+' . $levelUpHp - $increasedHp .')';
                $increasedAttackPoint = ' (+' . $levelUpAttackPoint - $increasedAttackPoint . ')';
            }

            $levelStatuses[] = [
                'level'                 => $value->level,
                'required_coin'         => $value->required_coin,
                'level_up_hp'           => $levelUpHp.$increasedHp,
                'level_up_attack_power' => $levelUpAttackPoint.$increasedAttackPoint
            ];

            $increasedHp = $levelUpHp;
            $increasedAttackPoint = $levelUpAttackPoint;
        }

        return $levelStatuses;
    }

    public function getRankUpStatusesList(): array
    {
        $mstUnit = $this->getMstModel();

        $mstUnitRankUp = MstUnitRankUp::query()
            ->where('unit_label', $mstUnit->unit_label)
            ->get();

        $mstConfig = MstConfig::query()
            ->where('key', MstConfigConstant::UNIT_RANKUP_COEFFICIENT_PERCENT)
            ->first();

        $unitRankupCoefficientPercent = 1;
        if (empty($mstConfig) === false) {
            $unitRankupCoefficientPercent = $mstConfig->value;
        }

        $rankUpStatuses = [];
        foreach ($mstUnitRankUp as $value) {
            $rankUpStatuses[] = [
                'rank'                  => $value->rank,
                'amount'                => $value->amount,
                'require_level'         => $value->require_level,
                'rank_up_hp'            => round($mstUnit->max_hp * $unitRankupCoefficientPercent, 0, PHP_ROUND_HALF_DOWN),
                'rank_up_attack_power'  => round($mstUnit->max_attack_power * $unitRankupCoefficientPercent, 0, PHP_ROUND_HALF_DOWN)
            ];
        }

        return $rankUpStatuses;
    }

    public function getGradeUpStatusesList(): array
    {
        $mstUnit = $this->getMstModel();

        $mstUnitGradeUp = MstUnitGradeUp::query()
            ->where('unit_label', $mstUnit->unit_label)
            ->get();

        $mstUnitCoefficent = MstUnitGradeCoefficient::query()
            ->get();

        $coefficentData = [];
        foreach ($mstUnitCoefficent as $value) {
            $coefficentData[$value->grade_level] = $value->coefficient;
        }

        $gradeUpstatuses = [];
        foreach ($mstUnitGradeUp as $value) {
            $coefficent = $coefficentData[$value->grade_level];
            $gradeUpstatuses[] = [
                'grade_level'           => $value->grade_level,
                'require_amount'        => $value->require_amount,
                'grade_up_hp'           => round(($mstUnit->max_hp - $mstUnit->mim_hp) * 0.5 * $coefficent, 0, PHP_ROUND_HALF_DOWN),
                'grade_up_attack_power' => round(($mstUnit->max_attack_power - $mstUnit->min_attack_power) * 0.5 * $coefficent, 0, PHP_ROUND_HALF_DOWN)
            ];
        }

        return $gradeUpstatuses;
    }

}
